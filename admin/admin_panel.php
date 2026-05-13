<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece adminler girebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

try {
    // 1. Toplam Kullanıcı Sayısı (Admin hariç)
    $toplamKullanici = $db->query("SELECT COUNT(*) FROM users WHERE RoleId != 1")->fetchColumn();

    // 2. Toplam Ürün Sayısı
    $toplamUrun = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // 3. Bekleyen Müşteri Siparişleri (Satış)
    $bekleyenSiparis = $db->query("SELECT COUNT(*) FROM orders WHERE Status = 'pending'")->fetchColumn();

    // 4. KRİTİK STOK SAYISI
    $kritikStokSorgu = $db->query("SELECT COUNT(*) FROM stocks WHERE Quantity <= MinStock");
    $kritikStokSayisi = $kritikStokSorgu->fetchColumn();

    // 5. YENİ: BEKLEYEN SATIN ALMA EMİRLERİ (Tedarikçiden mal beklenenler)
    // Hocanın görmek istediği purchaseorders tablosundan çekiyoruz.
    $bekleyenTedarik = $db->query("SELECT COUNT(*) FROM purchaseorders WHERE Status = 'pending'")->fetchColumn();

    // Son Siparişler (Satışlar)
    $sonSiparisler = $db->query("
        SELECT o.Id, o.OrderAt, o.TotalPrice, o.Status, o.ReceiverName, o.PaymentMethod, 
               CONCAT(u.FName, ' ', u.LName) as UserName 
        FROM orders o 
        LEFT JOIN users u ON o.UserId = u.Id 
        ORDER BY o.Id DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NalburDükkan - Yönetim Paneli</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; margin: 0; padding: 0; }
        .admin-content { margin-left: 260px; padding: 30px; font-family: 'Segoe UI', sans-serif; }
        /* Kart sayısını 5'e çıkardık, grid yapısını ayarlayalım */
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #ff6600; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: 0.3s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        /* Özel Renkli Kartlar */
        .card.critical { border-left-color: #ef4444; } /* Stok bittiyse kırmızı */
        .card.purchase { border-left-color: #8b5cf6; }  /* Tedarik için mor */
        .card.sales { border-left-color: #3b82f6; }     /* Siparişler için mavi */

        .card-info h3 { margin: 0; font-size: 24px; color: #1e293b; }
        .card-info p { margin: 5px 0 0; color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: bold; }
        .card-icon { font-size: 28px; color: #cbd5e1; }

        .admin-table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #f8fafc; color: #475569; padding: 12px; text-align: left; font-size: 14px; border-bottom: 2px solid #e2e8f0; }
        .admin-table td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 14px; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-completed { background: #dcfce3; color: #166534; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-content">
        <h2 style="margin-top: 0; color: #1e293b;">Yönetim Paneli Özet</h2>
        
        <div class="dashboard-cards">
            <a href="kritik_stok_raporu.php" class="card critical">
                <div class="card-info">
                    <h3><?php echo $kritikStokSayisi; ?></h3>
                    <p>Kritik Stok</p>
                </div>
                <i class="fa-solid fa-triangle-exclamation card-icon" style="color: #fca5a5;"></i>
            </a>

            <a href="satinalma_listele.php" class="card purchase">
                <div class="card-info">
                    <h3><?php echo $bekleyenTedarik; ?></h3>
                    <p>Bekleyen Tedarik</p>
                </div>
                <i class="fa-solid fa-truck-ramp-box card-icon" style="color: #c4b5fd;"></i>
            </a>

            <a href="admin_siparisler.php" class="card sales">
                <div class="card-info">
                    <h3><?php echo $bekleyenSiparis; ?></h3>
                    <p>Bekleyen Sipariş</p>
                </div>
                <i class="fa-solid fa-cart-shopping card-icon" style="color: #93c5fd;"></i>
            </a>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $toplamUrun; ?></h3>
                    <p>Toplam Ürün</p>
                </div>
                <i class="fa-solid fa-tags card-icon"></i>
            </div>

            <div class="card">
                <div class="card-info">
                    <h3><?php echo $toplamKullanici; ?></h3>
                    <p>Müşteriler</p>
                </div>
                <i class="fa-solid fa-users card-icon"></i>
            </div>
        </div>

        <div class="admin-table-container">
            <h3 style="margin-top: 0; color: #334155;">Son Satış Siparişleri</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Müşteri / Alıcı</th>
                        <th>Ödeme</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sonSiparisler)): ?>
                        <tr><td colspan="7" style="text-align: center;">Henüz sipariş yok.</td></tr>
                    <?php else: ?>
                        <?php foreach($sonSiparisler as $siparis): ?>
                            <tr>
                                <td>#<?php echo $siparis['Id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($siparis['ReceiverName'] ?? $siparis['UserName']); ?></strong>
                                </td>
                                <td><?php echo $siparis['PaymentMethod']; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($siparis['OrderAt'])); ?></td>
                                <td><?php echo number_format($siparis['TotalPrice'], 2, ',', '.'); ?> TL</td>
                                <td>
                                    <?php 
                                        $s = $siparis['Status'];
                                        $class = ($s == 'pending') ? 'status-pending' : 'status-completed';
                                        $text = ($s == 'pending') ? 'Bekliyor' : 'Tamamlandı';
                                        echo "<span class='status-badge $class'>$text</span>";
                                    ?>
                                </td>
                                <td>
                                    <a href="admin_siparis_detay.php?id=<?php echo $siparis['Id']; ?>" style="color: #2563eb;">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>