<?php
session_start();
include 'baglan.php';

// Güvenlik Kontrolü: Sadece adminler girebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

// --- 1. POST İŞLEMİ: FORM GÖNDERİLDİĞİNDE ÇALIŞACAK ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supplier_id'])) {
    try {
        $db->beginTransaction();

        $supplierId = $_POST['supplier_id'];
        $adminId = $_SESSION['user_id'];
        
        // Ana Satın Alma Emrini Oluştur (Status: pending)
        $stmt = $db->prepare("INSERT INTO purchaseorders (SupplierId, CreatedByUserId, Status, TotalAmount) VALUES (?, ?, 'pending', 0)");
        $stmt->execute([$supplierId, $adminId]);
        $poId = $db->lastInsertId();

        $totalOrderAmount = 0;

        // Seçilen Ürünleri Döngüye Al
        foreach ($_POST['qty'] as $productId => $qty) {
            $qty = intval($qty);
            if ($qty > 0) {
                // Ürünün fiyat bilgisini products tablosundan çekelim (veya sabit bir maliyet)
                $pSt = $db->prepare("SELECT Price FROM products WHERE Id = ?");
                $pSt->execute([$productId]);
                $product = $pSt->fetch(PDO::FETCH_ASSOC);
                
                // Tedarik maliyeti genelde satış fiyatının %70'idir diye kurgulayalım (örnek)
                $unitCost = $product['Price'] * 0.7; 
                $totalOrderAmount += ($qty * $unitCost);

                $ins = $db->prepare("INSERT INTO purchaseorderproducts (PurchaseOrderId, ProductId, OrderedQty, ReceivedQty, UnitCost) VALUES (?, ?, ?, 0, ?)");
                $ins->execute([$poId, $productId, $qty, $unitCost]);
            }
        }

        // Toplam Tutarı Güncelle
        $db->prepare("UPDATE purchaseorders SET TotalAmount = ? WHERE Id = ?")->execute([$totalOrderAmount, $poId]);

        $db->commit();
        header("Location: satinalma_listele.php?mesaj=basarili");
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $hataMesaji = "Hata oluştu: " . $e->getMessage();
    }
}

// --- 2. VERİLERİ ÇEKME ---
// Ürünler ve Stok Durumları
$urunler = $db->query("SELECT p.Id, p.Name, p.ImagePath, p.Price, s.Quantity, s.MinStock 
                       FROM products p 
                       LEFT JOIN stocks s ON p.Id = s.ProductId 
                       WHERE p.IsActive = 1 
                       ORDER BY p.Name ASC")->fetchAll(PDO::FETCH_ASSOC);

// TEDARİKÇİLER (Hata payını sıfıra indirmek için FETCH_ASSOC ekledik)
$tedarikciler = $db->query("SELECT Id, Name FROM suppliers ORDER BY Name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Tedarik Oluştur - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .purchase-container { margin-left: 260px; padding: 30px; background: #f8fafc; min-height: 100vh; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .search-box { position: relative; width: 350px; }
        .search-box input { width: 100%; padding: 12px 40px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; }
        .search-box i { position: absolute; left: 15px; top: 14px; color: #94a3b8; }
        
        .purchase-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .supplier-select { padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; width: 350px; font-weight: 600; background: #fff; }

        .modern-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 13px; }
        .modern-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; }
        
        .product-info { display: flex; align-items: center; gap: 12px; }
        .product-img { width: 40px; height: 40px; border-radius: 4px; object-fit: contain; background: #f8fafc; }
        
        .stock-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .stock-low { background: #fee2e2; color: #ef4444; }
        .stock-ok { background: #dcfce3; color: #166534; }

        .qty-input { width: 70px; padding: 8px; border: 2px solid #e2e8f0; border-radius: 6px; text-align: center; font-weight: bold; }
        .qty-input:focus { border-color: #ff6600; outline: none; background: #fff7ed; }

        .submit-bar { position: sticky; bottom: 20px; background: #1e293b; color: white; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-top: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-order { background: #ff6600; color: white; border: none; padding: 12px 35px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-order:hover { transform: scale(1.03); background: #e65c00; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="purchase-container">
        <div class="header-flex">
            <div>
                <h2 style="margin:0;"><i class="fa-solid fa-cart-flatbed-suitcases"></i> Yeni Tedarik Oluştur</h2>
                <p style="color:#64748b;">Eksik ürünleri belirleyin ve tedarikçiye bildirin.</p>
            </div>
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="productSearch" placeholder="Ürün ara...">
            </div>
        </div>

        <?php if(isset($hataMesaji)) echo "<p style='color:red;'>$hataMesaji</p>"; ?>

        <form action="" method="POST" class="purchase-card">
            <div style="margin-bottom: 30px;">
                <label style="display:block; margin-bottom:8px; font-weight:bold;">Siparişi Göndereceğiniz Tedarikçi:</label>
                <select name="supplier_id" class="supplier-select" required>
                    <option value="">--- Tedarikçi Seçiniz ---</option>
                    
                    <?php if (empty($tedarikciler)): ?>
                        <option value="" disabled>Veritabanında kayıtlı tedarikçi bulunamadı!</option>
                    <?php else: ?>
                        <?php foreach($tedarikciler as $t): ?>
                            <option value="<?php echo $t['Id']; ?>">
                                <?php echo htmlspecialchars($t['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <table class="modern-table" id="productTable">
                <thead>
                    <tr>
                        <th>Ürün Bilgisi</th>
                        <th>Mevcut Stok</th>
                        <th>Durum</th>
                        <th>Sipariş Adeti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($urunler as $u): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="<?php echo $u['ImagePath']; ?>" class="product-img">
                                <span class="product-name"><?php echo htmlspecialchars($u['Name']); ?></span>
                            </div>
                        </td>
                        <td style="font-weight:600;"><?php echo $u['Quantity'] ?? 0; ?> Adet</td>
                        <td>
                            <?php if(($u['Quantity'] ?? 0) <= ($u['MinStock'] ?? 5)): ?>
                                <span class="stock-badge stock-low">Kritik Stok</span>
                            <?php else: ?>
                                <span class="stock-badge stock-ok">Yeterli</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" name="qty[<?php echo $u['Id']; ?>]" class="qty-input" value="0" min="0">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="submit-bar">
                <span><i class="fa-solid fa-circle-info"></i> Sadece sipariş adeti 1 ve üzeri olan ürünler emre dahil edilecektir.</span>
                <button type="submit" class="btn-order">Tedarik Emrini Onayla</button>
            </div>
        </form>
    </main>

    <script>
        // Ürün Arama Fonksiyonu
        document.getElementById('productSearch').addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let rows = document.querySelector("#productTable tbody").rows;
            for (let i = 0; i < rows.length; i++) {
                let name = rows[i].querySelector(".product-name").textContent.toUpperCase();
                rows[i].style.display = name.includes(filter) ? "" : "none";
            }
        });
    </script>

</body>
</html>