<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece admin yetkisi olanlar erişebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) { 
    header("Location: index.php"); 
    exit; 
}

// Veritabanı Gereksinimi: Alt sorgular ile Group By mantığı kullanımı [cite: 103]
$sorgu = $db->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM vehicles WHERE ShipperId = s.Id) as VehicleCount,
           (SELECT COUNT(*) FROM drivers WHERE ShipperId = s.Id) as DriverCount
    FROM shippers s
    ORDER BY s.Name ASC
");
$firmalar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kargo Firmaları - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Butonun link olarak düzgün görünmesi için stil ekledik */
        .btn-add-kargo {
            background: #ff6600; 
            color: white; 
            text-decoration: none; 
            padding: 10px 20px; 
            border-radius: 8px; 
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-add-kargo:hover {
            background: #e65c00;
            box-shadow: 0 4px 12px rgba(255, 102, 0, 0.2);
        }
    </style>
</head>
<body style="background: #f8fafc;">

    <?php include 'admin_sidebar.php'; ?>

    <main style="margin-left: 260px; padding: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div class="page-title">
                <h2 style="margin:0;"><i class="fa-solid fa-truck-fast" style="color: #ff6600;"></i> Kargo Firmaları</h2>
                <p style="color: #64748b; margin: 5px 0 0;">Lojistik ortaklarınızı ve kapasitelerini yönetin.</p>
            </div>
            
            <a href="admin_kargo_ekle.php" class="btn-add-kargo">
                <i class="fa-solid fa-plus"></i> Yeni Firma Ekle
            </a>
        </div>

        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f1f5f9; text-align: left;">
                    <tr>
                        <th style="padding: 15px; color: #475569; font-size: 13px; text-transform: uppercase;">Firma Adı</th>
                        <th style="padding: 15px; color: #475569; font-size: 13px; text-transform: uppercase;">Telefon</th>
                        <th style="padding: 15px; color: #475569; font-size: 13px; text-transform: uppercase;">Araç Sayısı</th>
                        <th style="padding: 15px; color: #475569; font-size: 13px; text-transform: uppercase;">Sürücü Sayısı</th>
                        <th style="padding: 15px; color: #475569; font-size: 13px; text-transform: uppercase;">Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($firmalar)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">Henüz tanımlı kargo firması bulunamadı.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($firmalar as $f): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 15px; font-weight: bold; color: #1e293b;"><?php echo htmlspecialchars($f['Name']); ?></td>
                            <td style="padding: 15px; color: #475569;"><?php echo !empty($f['Phone']) ? $f['Phone'] : '-'; ?></td>
                            <td style="padding: 15px;">
                                <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    <?php echo $f['VehicleCount']; ?> Araç
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <span style="background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    <?php echo $f['DriverCount']; ?> Sürücü
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <span style="display: flex; align-items: center; gap: 5px; color: #22c55e; font-size: 14px; font-weight: 500;">
                                    <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Aktif
                                </span>
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