<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) { header("Location: index.php"); exit; }

// Veritabanı Gereksinimi: Araçları kargo firmalarıyla birleştirerek çekiyoruz [cite: 102]
$sorgu = $db->query("
    SELECT v.*, s.Name as ShipperName 
    FROM vehicles v 
    JOIN shippers s ON v.ShipperId = s.Id
    ORDER BY v.Id DESC
");
$araclar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Araç Yönetimi - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; }
        .list-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .modern-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .btn-add { background: #ff6600; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <main class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2><i class="fa-solid fa-bus" style="color: #ff6600;"></i> Araç Yönetimi</h2>
                <p style="color: #64748b;">Teslimat filonuzdaki araçların plaka ve marka kayıtları.</p>
            </div>
            <a href="admin_arac_ekle.php" class="btn-add"><i class="fa-solid fa-plus"></i> Yeni Araç Ekle</a>
        </div>

        <div class="list-card">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Plaka</th>
                        <th>Marka / Model</th>
                        <th>Bağlı Firma</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($araclar)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 30px; color: #94a3b8;">Henüz kayıtlı araç bulunmuyor.</td></tr>
                    <?php else: ?>
                        <?php foreach($araclar as $a): ?>
                        <tr>
                            <td style="font-family: 'Courier New', monospace; font-weight: bold; letter-spacing: 1px;"><?php echo htmlspecialchars($a['PlateNumber']); ?></td>
                            <td><?php echo htmlspecialchars($a['Brand'] . " " . $a['Model']); ?></td>
                            <td style="color: #64748b;"><?php echo htmlspecialchars($a['ShipperName']); ?></td>
                            <td><span style="color: #22c55e;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Görevde</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>