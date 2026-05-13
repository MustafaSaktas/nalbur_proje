<?php
session_start();
include 'baglan.php';

// Güvenlik: Kullanıcı giriş yapmamışsa girişe yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Sadece bu kullanıcının siparişlerini en yeniden eskiye doğru çekiyoruz
$st = $db->prepare("SELECT * FROM orders WHERE UserId = ? ORDER BY Id DESC");
$st->execute([$userId]);
$siparisler = $st->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Siparişlerim - NalburDükkan</title>
    <!-- İşte hayat kurtaran ana CSS bağlantımız -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8fafc; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        .siparis-container {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            min-height: 50vh;
        }
        .siparis-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .siparis-table th {
            background-color: #f8fafc;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
        }
        .siparis-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }
        .siparis-table tr:hover {
            background-color: #f8fafc;
        }
        
        /* Müşteri tarafı durum rozetleri */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-pending { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        .badge-shipped { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-completed { background: #dcfce3; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

    <!-- Üst Menüyü Şimdi Çağırıyoruz -->
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="siparis-container">
            <h2 style="color: #1e293b; margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <i class="fa-solid fa-box-open" style="color: #ff6600;"></i> Sipariş Geçmişim
            </h2>

            <?php if (empty($siparisler)): ?>
                <div style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fa-solid fa-cart-arrow-down" style="font-size: 40px; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <p style="font-size: 18px;">Henüz hiç sipariş vermediniz.</p>
                    <a href="index.php" style="display: inline-block; margin-top: 15px; background: #ff6600; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Alışverişe Başla</a>
                </div>
            <?php else: ?>
                <table class="siparis-table">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Tarih</th>
                            <th>Tutar</th>
                            <th>Ödeme Yöntemi</th>
                            <th>Sipariş Durumu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($siparisler as $siparis): ?>
                            <tr>
                                <td><strong>#<?php echo $siparis['Id']; ?></strong></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($siparis['OrderAt'])); ?></td>
                                <td><strong><?php echo number_format($siparis['TotalPrice'], 2, ',', '.'); ?> TL</strong></td>
                                <td><?php echo htmlspecialchars($siparis['PaymentMethod'] ?? 'Belirtilmedi'); ?></td>
                                <td>
                                    <?php 
                                        if($siparis['Status'] == 'pending'){
                                            echo '<span class="status-badge badge-pending"><i class="fa-solid fa-clock"></i> Onay Bekliyor</span>';
                                        } elseif($siparis['Status'] == 'shipped'){
                                            echo '<span class="status-badge badge-shipped"><i class="fa-solid fa-truck-fast"></i> Kargoya Verildi</span>';
                                        } elseif($siparis['Status'] == 'completed'){
                                            echo '<span class="status-badge badge-completed"><i class="fa-solid fa-check-double"></i> Teslim Edildi</span>';
                                        } else {
                                            echo htmlspecialchars($siparis['Status']);
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alt Kısım -->
    <?php include 'footer.php'; ?>

</body>
</html>