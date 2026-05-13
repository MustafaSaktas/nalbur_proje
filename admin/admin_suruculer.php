<?php
session_start();
include 'baglan.php';

// Güvenlik kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) { 
    header("Location: index.php"); 
    exit; 
}

// TEKNİK KRİTER: Join ve Karakter Fonksiyonu (UPPER) kullanımı
$sorgu = $db->query("
    SELECT d.Id, d.RegistrationNo, UPPER(d.FName) as FirstName, UPPER(d.LName) as LastName, d.Age, s.Name as ShipperName
    FROM drivers d
    JOIN shippers s ON d.ShipperId = s.Id
    ORDER BY d.Id DESC
");
$suruculer = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sürücü Kayıtları - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; }
        .list-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .modern-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .btn-add { background: #ff6600; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <main class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h2><i class="fa-solid fa-id-card" style="color: #ff6600;"></i> Sürücü Kayıtları</h2>
                <p style="color: #64748b;">Lojistik operasyonlarında görevli sürücülerin sicil ve yaş bilgileri.</p>
            </div>
            <a href="admin_surucu_ekle.php" class="btn-add"><i class="fa-solid fa-user-plus"></i> Yeni Sürücü Ekle</a>
        </div>

        <div class="list-card">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Sicil No</th>
                        <th>Ad Soyad (UPPER)</th>
                        <th>Yaş</th>
                        <th>Bağlı Firma</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($suruculer)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">Henüz kayıtlı sürücü bulunmuyor.</td></tr>
                    <?php else: ?>
                        <?php foreach($suruculer as $s): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: bold;"><?php echo htmlspecialchars($s['RegistrationNo']); ?></td>
                            <td><strong><?php echo $s['FirstName'] . " " . $s['LastName']; ?></strong></td>
                            <td><?php echo $s['Age']; ?></td>
                            <td style="color: #64748b;"><?php echo htmlspecialchars($s['ShipperName']); ?></td>
                            <td><a href="#" style="color: #64748b;"><i class="fa-solid fa-pen-to-square"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>