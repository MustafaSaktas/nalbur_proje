<?php
session_start();
include 'baglan.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

$poId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- STOK KABUL İŞLEMİ (Butona basıldığında çalışır) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kabul_et'])) {
    try {
        $db->beginTransaction();

        // 1. Bu siparişteki ürünleri çek
        $urunlerSorgu = $db->prepare("SELECT * FROM purchaseorderproducts WHERE PurchaseOrderId = ?");
        $urunlerSorgu->execute([$poId]);
        $siparisUrunleri = $urunlerSorgu->fetchAll(PDO::FETCH_ASSOC);

        foreach ($siparisUrunleri as $item) {
            $adet = $item['OrderedQty'];
            $productId = $item['ProductId'];

            // 2. STOCKS tablosunu güncelle (Mevcut miktara ekle)
            $stokGuncelle = $db->prepare("UPDATE stocks SET Quantity = Quantity + ? WHERE ProductId = ?");
            $stokGuncelle->execute([$adet, $productId]);

            // 3. PRODUCTS tablosundaki Stock sütununu senkronize et
            $urunGuncelle = $db->prepare("UPDATE products SET Stock = Stock + ? WHERE Id = ?");
            $urunGuncelle->execute([$adet, $productId]);

            // 4. Bu siparişte kaç adet teslim alındığını işaretle
            $popGuncelle = $db->prepare("UPDATE purchaseorderproducts SET ReceivedQty = ? WHERE Id = ?");
            $popGuncelle->execute([$adet, $item['Id']]);
        }

        // 5. Satın Alma Emrinin durumunu 'received' yap
        $statusGuncelle = $db->prepare("UPDATE purchaseorders SET Status = 'received' WHERE Id = ?");
        $statusGuncelle->execute([$poId]);

        $db->commit();
        $mesaj = "success";
    } catch (Exception $e) {
        $db->rollBack();
        $mesaj = "error";
        $hata = $e->getMessage();
    }
}

// --- VERİLERİ ÇEKME ---
// 1. Ana Sipariş Bilgisi
$poSorgu = $db->prepare("
    SELECT po.*, s.Name as SupplierName, s.Email, s.Phone 
    FROM purchaseorders po 
    JOIN suppliers s ON po.SupplierId = s.Id 
    WHERE po.Id = ?
");
$poSorgu->execute([$poId]);
$siparis = $poSorgu->fetch(PDO::FETCH_ASSOC);

if (!$siparis) { die("Sipariş bulunamadı!"); }

// 2. Siparişe Ait Ürünler
$urunlerSorgu = $db->prepare("
    SELECT pop.*, p.Name as ProductName, p.ImagePath 
    FROM purchaseorderproducts pop 
    JOIN products p ON pop.ProductId = p.Id 
    WHERE pop.PurchaseOrderId = ?
");
$urunlerSorgu->execute([$poId]);
$urunler = $urunlerSorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tedarik Detayı #<?php echo $poId; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 30px; background: #f8fafc; min-height: 100vh; }
        .detail-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; }
        .info-item h4 { margin: 0 0 5px 0; color: #64748b; font-size: 13px; text-transform: uppercase; }
        .info-item p { margin: 0; font-size: 16px; font-weight: 600; color: #1e293b; }
        
        .modern-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .modern-table th { text-align: left; padding: 12px; background: #f1f5f9; color: #475569; }
        .modern-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
        
        .product-img { width: 40px; height: 40px; border-radius: 4px; object-fit: contain; vertical-align: middle; margin-right: 10px; }
        
        .action-bar { margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; }
        .btn-accept { background: #22c55e; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-accept:hover { background: #16a34a; }
        .status-msg { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-container">
        <h2 style="margin-bottom: 20px;"><a href="satinalma_listele.php" style="color:#64748b;"><i class="fa-solid fa-arrow-left"></i></a> Tedarik Detayı #<?php echo $poId; ?></h2>

        <?php if(isset($mesaj) && $mesaj == 'success'): ?>
            <div class="status-msg" style="background:#dcfce3; color:#166534;">Ürünler başarıyla depoya kabul edildi ve stoklar güncellendi!</div>
        <?php endif; ?>

        <div class="detail-card">
            <div class="info-grid">
                <div class="info-item">
                    <h4>Tedarikçi Bilgisi</h4>
                    <p><?php echo htmlspecialchars($siparis['SupplierName']); ?></p>
                    <small style="color:#64748b;"><?php echo $siparis['Email']; ?> | <?php echo $siparis['Phone']; ?></small>
                </div>
                <div class="info-item" style="text-align: right;">
                    <h4>Sipariş Durumu / Tarih</h4>
                    <span class="badge" style="background:#e2e8f0; padding:5px 10px; border-radius:4px;"><?php echo strtoupper($siparis['Status']); ?></span>
                    <p><?php echo date('d.m.Y H:i', strtotime($siparis['CreatedAt'])); ?></p>
                </div>
            </div>

            <h3>Sipariş İçeriği</h3>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Birim Maliyet</th>
                        <th>Sipariş Adeti</th>
                        <th>Teslim Alınan</th>
                        <th>Ara Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($urunler as $u): ?>
                    <tr>
                        <td>
                            <img src="<?php echo $u['ImagePath']; ?>" class="product-img">
                            <?php echo htmlspecialchars($u['ProductName']); ?>
                        </td>
                        <td><?php echo number_format($u['UnitCost'], 2, ',', '.'); ?> TL</td>
                        <td><strong><?php echo $u['OrderedQty']; ?> Adet</strong></td>
                        <td><?php echo ($siparis['Status'] == 'received') ? $u['OrderedQty'] : '0'; ?></td>
                        <td><?php echo number_format($u['UnitCost'] * $u['OrderedQty'], 2, ',', '.'); ?> TL</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc; font-weight:bold;">
                        <td colspan="4" style="text-align: right; padding: 15px;">TOPLAM TUTAR:</td>
                        <td style="padding: 15px; color:#ff6600; font-size:18px;"><?php echo number_format($siparis['TotalAmount'], 2, ',', '.'); ?> TL</td>
                    </tr>
                </tfoot>
            </table>

            <?php if($siparis['Status'] == 'pending'): ?>
            <form method="POST" class="action-bar">
                <p style="color:#64748b; font-size:13px; margin-right: auto; align-self: center;">
                    <i class="fa-solid fa-circle-info"></i> "Kabul Et" butonuna bastığınızda stoklar otomatik artacaktır.
                </p>
                <button type="submit" name="kabul_et" class="btn-accept" onclick="return confirm('Malların depoya ulaştığını ve stokların güncellenmesini onaylıyor musunuz?')">
                    <i class="fa-solid fa-check-double"></i> Malları Depoya Kabul Et
                </button>
            </form>
            <?php else: ?>
            <div class="action-bar">
                <span style="color:#16a34a; font-weight:bold;"><i class="fa-solid fa-circle-check"></i> Bu sevkiyat depoya kabul edilmiştir.</span>
            </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>