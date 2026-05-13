<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece admin yetkisi olanlar (Role 1) görebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

// TEDARİK EMİRLERİNİ ÇEKİYORUZ
// Ders gereksinimi: purchaseorders ve suppliers tablolarını Join ile birleştiriyoruz.
$sorgu = $db->query("
    SELECT po.*, s.Name as SupplierName 
    FROM purchaseorders po
    JOIN suppliers s ON po.SupplierId = s.Id
    ORDER BY po.CreatedAt DESC
");
$emirler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satın Alma Emirleri - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin yerleşimi: Sidebar için soldan 260px boşluk bırakıyoruz */
        .admin-container { margin-left: 260px; padding: 30px; background: #f8fafc; min-height: 100vh; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h2 { margin: 0; color: #1e293b; font-size: 24px; }
        .page-title p { margin: 5px 0 0; color: #64748b; }

        .list-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .modern-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; }
        .modern-table tr:hover { background: #f8fafc; }

        /* Durum Rozetleri */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-pending { background: #fef3c7; color: #d97706; } 
        .badge-received { background: #dcfce3; color: #166534; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }

        .btn-action { color: #ff6600; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: 0.3s; }
        .btn-action:hover { color: #cc5200; text-decoration: underline; }

        .btn-new { background: #ff6600; color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-new:hover { background: #e65c00; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-container">
        <div class="page-header">
            <div class="page-title">
                <h2><i class="fa-solid fa-file-invoice-dollar" style="color: #ff6600;"></i> Satın Alma Emirleri</h2>
                <p>Dükkan stoğunu yenilemek için tedarikçilere verilen emirler.</p>
            </div>
            <a href="satinalma_olustur.php" class="btn-new">
                <i class="fa-solid fa-plus"></i> Yeni Emir Oluştur
            </a>
        </div>

        <div class="list-card">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tedarikçi Firma</th>
                        <th>Durum</th>
                        <th>Toplam Tutar</th>
                        <th>Oluşturma Tarihi</th>
                        <th style="text-align: center;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($emirler)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-folder-open fa-2x"></i><br>Henüz bir satın alma emri bulunmuyor.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($emirler as $e): ?>
                        <tr>
                            <td><strong>#<?php echo $e['Id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($e['SupplierName']); ?></td>
                            <td>
                                <?php 
                                    $status = strtolower($e['Status']);
                                    $badgeClass = ($status == 'pending') ? 'badge-pending' : (($status == 'received') ? 'badge-received' : 'badge-cancelled');
                                    $statusText = ($status == 'pending') ? 'Bekliyor' : (($status == 'received') ? 'Kabul Edildi' : 'İptal');
                                    echo "<span class='badge $badgeClass'>$statusText</span>";
                                ?>
                            </td>
                            <td style="font-weight: bold; color: #1e293b;">
                                <?php echo number_format($e['TotalAmount'], 2, ',', '.'); ?> TL
                            </td>
                            <td>
                                <?php 
                                    // Ders gereksinimi: Tarih fonksiyonu kullanımı
                                    echo date('d.m.Y H:i', strtotime($e['CreatedAt'])); 
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="satinalma_detay.php?id=<?php echo $e['Id']; ?>" class="btn-action">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i> İncele / Stok Kabul
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