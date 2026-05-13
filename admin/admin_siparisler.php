<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece adminler girebilir
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

// TÜM SİPARİŞLERİ ÇEK (LIMIT yok)
try {
    $siparisler = $db->query("
        SELECT o.Id, o.OrderAt, o.TotalPrice, o.Status, o.ReceiverName, o.PaymentMethod, 
               CONCAT(u.FName, ' ', u.LName) as UserName 
        FROM orders o 
        LEFT JOIN users u ON o.UserId = u.Id 
        ORDER BY o.Id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tüm Siparişler - Admin Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif;}
        .admin-content { margin-left: 260px; padding: 30px; }
        .admin-table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #f8fafc; color: #475569; padding: 12px; text-align: left; font-size: 14px; border-bottom: 2px solid #e2e8f0; }
        .admin-table td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 14px; }
        .admin-table tr:hover { background: #f8fafc; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-shipped { background: #dbeafe; color: #2563eb; }
        .status-completed { background: #dcfce3; color: #166534; }
        .btn-incele { color: #2563eb; text-decoration: none; font-weight: bold; }
        .btn-incele:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-content">
        <h2 style="margin-top: 0; color: #1e293b;"><i class="fa-solid fa-cart-shopping"></i> Tüm Siparişler</h2>
        
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Alıcı Adı</th>
                        <th>Ödeme Türü</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siparisler)): ?>
                        <tr><td colspan="7" style="text-align: center;">Henüz hiç sipariş yok.</td></tr>
                    <?php else: ?>
                        <?php foreach($siparisler as $siparis): ?>
                            <tr>
                                <td><strong>#<?php echo $siparis['Id']; ?></strong></td>
                                <td><?php echo !empty($siparis['ReceiverName']) ? htmlspecialchars($siparis['ReceiverName']) : htmlspecialchars($siparis['UserName']); ?></td>
                                <td><i class="fa-solid fa-credit-card"></i> <?php echo htmlspecialchars($siparis['PaymentMethod'] ?? 'Belirtilmedi'); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($siparis['OrderAt'])); ?></td>
                                <td><strong><?php echo number_format($siparis['TotalPrice'], 2, ',', '.'); ?> TL</strong></td>
                                <td>
                                    <?php 
                                        if($siparis['Status'] == 'pending') echo '<span class="status-badge status-pending">Bekliyor</span>';
                                        elseif($siparis['Status'] == 'shipped') echo '<span class="status-badge status-shipped">Kargoda</span>';
                                        elseif($siparis['Status'] == 'completed') echo '<span class="status-badge status-completed">Tamamlandı</span>';
                                        else echo htmlspecialchars($siparis['Status']);
                                    ?>
                                </td>
                                <td>
                                    <a href="admin_siparis_detay.php?id=<?php echo $siparis['Id']; ?>" class="btn-incele">
                                        <i class="fa-solid fa-magnifying-glass"></i> İncele
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