<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$mesaj = "";

// 1. LOJİSTİK VERİLERİNİ ÇEK (Seçim kutuları için)
$kargoFirmalari = $db->query("SELECT Id, Name FROM shippers ORDER BY Name ASC")->fetchAll(PDO::FETCH_ASSOC);
$araclar = $db->query("SELECT Id, PlateNumber, Brand FROM vehicles ORDER BY PlateNumber ASC")->fetchAll(PDO::FETCH_ASSOC);
$suruculer = $db->query("SELECT Id, FName, LName FROM drivers ORDER BY FName ASC")->fetchAll(PDO::FETCH_ASSOC);

// 2. GÜNCELLEME İŞLEMİ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yeni_durum'])) {
    $yeniDurum = $_POST['yeni_durum'];
    
    // Lojistik bilgilerini al (Sadece kargolandı durumunda kaydedilir)
    $shipperId = !empty($_POST['shipper_id']) ? $_POST['shipper_id'] : null;
    $vehicleId = !empty($_POST['vehicle_id']) ? $_POST['vehicle_id'] : null;
    $driverId = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;

    // SQL UPDATE: Durum ve Lojistik bilgilerini beraber güncelliyoruz
    $guncelle = $db->prepare("UPDATE orders SET Status = ?, ShipperId = ?, VehicleId = ?, DriverId = ?, UpdatedAt = CURRENT_TIMESTAMP WHERE Id = ?");
    
    if($guncelle->execute([$yeniDurum, $shipperId, $vehicleId, $driverId, $id])){
        $mesaj = "<div style='background: #dcfce3; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;'><i class='fa-solid fa-circle-check'></i> Sipariş ve lojistik bilgileri başarıyla güncellendi!</div>";
    }
}

// 3. SİPARİŞ BİLGİLERİNİ ÇEK (Join ile kargo isimlerini de alıyoruz ki üst kartta gösterelim)
$st = $db->prepare("
    SELECT o.*, CONCAT(u.FName, ' ', u.LName) as UserName, u.Email,
           s.Name as ShipperName, v.PlateNumber as VehiclePlate, CONCAT(d.FName, ' ', d.LName) as DriverName
    FROM orders o 
    LEFT JOIN users u ON o.UserId = u.Id 
    LEFT JOIN shippers s ON o.ShipperId = s.Id
    LEFT JOIN vehicles v ON o.VehicleId = v.Id
    LEFT JOIN drivers d ON o.DriverId = d.Id
    WHERE o.Id = ?
");
$st->execute([$id]);
$siparis = $st->fetch(PDO::FETCH_ASSOC);

if (!$siparis) { die("Böyle bir sipariş bulunamadı."); }

// 4. SİPARİŞ ÜRÜNLERİNİ ÇEK
$urunSt = $db->prepare("SELECT od.*, p.Name as ProductName, p.ImagePath, p.SKU FROM orderdetails od LEFT JOIN products p ON od.ProductId = p.Id WHERE od.OrderId = ?");
$urunSt->execute([$id]);
$urunler = $urunSt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Yönetimi #<?php echo $id; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .admin-content { margin-left: 260px; padding: 30px; }
        .grid-container { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
        .info-row { margin-bottom: 12px; font-size: 14px; color: #475569; }
        .info-row strong { color: #1e293b; width: 130px; display: inline-block; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8fafc; padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        .table td { padding: 15px 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        select { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 15px; background: #f8fafc; }
        .btn-update { background: #ff6600; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-update:hover { background: #e65c00; }
        #lojistik-panel { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; display: none; }
        #lojistik-panel label { font-size: 11px; font-weight: bold; color: #64748b; margin-bottom: 5px; display: block; text-transform: uppercase; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-content">
        <a href="admin_siparisler.php" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; font-size: 14px;"><i class="fa-solid fa-arrow-left"></i> Sipariş Listesine Dön</a>
        
        <?php echo $mesaj; ?>

        <div class="grid-container">
            <div>
                <div class="card">
                    <h3><i class="fa-solid fa-truck-ramp-box" style="color: #ff6600;"></i> Teslimat Bilgileri</h3>
                    <div class="info-row"><strong>Alıcı:</strong> <?php echo htmlspecialchars($siparis['ReceiverName'] ?: $siparis['UserName']); ?></div>
                    <div class="info-row"><strong>Telefon:</strong> <?php echo htmlspecialchars($siparis['Phone']); ?></div>
                    <div class="info-row"><strong>Adres:</strong> <?php echo nl2br(htmlspecialchars($siparis['Address'])); ?></div>
                    <div class="info-row"><strong>Ödeme:</strong> <span style="color: #166534; font-weight: 600;"><?php echo htmlspecialchars($siparis['PaymentMethod']); ?></span></div>
                    <div class="info-row"><strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($siparis['OrderAt'])); ?></div>
                    
                    <?php if($siparis['ShipperName']): ?>
                    <hr style="border:0; border-top:1px dashed #e2e8f0; margin:15px 0;">
                    <div class="info-row"><strong>Kargo:</strong> <?php echo htmlspecialchars($siparis['ShipperName']); ?></div>
                    <div class="info-row"><strong>Araç:</strong> <?php echo htmlspecialchars($siparis['VehiclePlate']); ?></div>
                    <div class="info-row"><strong>Sürücü:</strong> <?php echo htmlspecialchars($siparis['DriverName']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3><i class="fa-solid fa-pen-to-square"></i> Durum ve Lojistik Atama</h3>
                    <form method="POST">
                        <label style="font-size: 12px; font-weight: bold; color: #64748b; margin-bottom: 5px; display: block;">SİPARİŞ DURUMU</label>
                        <select name="yeni_durum" id="statusSelect">
                            <option value="pending" <?php if($siparis['Status'] == 'pending') echo 'selected'; ?>>Bekliyor</option>
                            <option value="shipped" <?php if($siparis['Status'] == 'shipped') echo 'selected'; ?>>Kargolandı</option>
                            <option value="completed" <?php if($siparis['Status'] == 'completed') echo 'selected'; ?>>Tamamlandı</option>
                            <option value="cancelled" <?php if($siparis['Status'] == 'cancelled') echo 'selected'; ?>>İptal Edildi</option>
                        </select>

                        <div id="lojistik-panel">
                            <label>Kargo Firması</label>
                            <select name="shipper_id">
                                <option value="">Firma Seçin...</option>
                                <?php foreach($kargoFirmalari as $k): ?>
                                    <option value="<?= $k['Id'] ?>" <?php if($siparis['ShipperId'] == $k['Id']) echo 'selected'; ?>><?= htmlspecialchars($k['Name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label>Teslimat Aracı</label>
                            <select name="vehicle_id">
                                <option value="">Araç Seçin...</option>
                                <?php foreach($araclar as $a): ?>
                                    <option value="<?= $a['Id'] ?>" <?php if($siparis['VehicleId'] == $a['Id']) echo 'selected'; ?>><?= htmlspecialchars($a['PlateNumber'] . " - " . $a['Brand']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label>Görevli Sürücü</label>
                            <select name="driver_id">
                                <option value="">Sürücü Seçin...</option>
                                <?php foreach($suruculer as $s): ?>
                                    <option value="<?= $s['Id'] ?>" <?php if($siparis['DriverId'] == $s['Id']) echo 'selected'; ?>><?= htmlspecialchars($s['FName'] . " " . $s['LName']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-update">Değişiklikleri Kaydet</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-basket-shopping"></i> Satın Alınan Ürünler</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>SKU</th>
                            <th>Adet</th>
                            <th style="text-align: right;">Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($urunler as $u): ?>
                        <tr>
                            <td style="display:flex; align-items:center; gap:12px;">
                                <img src="<?php echo htmlspecialchars($u['ImagePath']); ?>" style="width:45px; height:45px; border-radius:6px; object-fit:cover; border:1px solid #f1f5f9;">
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($u['ProductName']); ?></div>
                                    <div style="font-size: 12px; color: #94a3b8;">Birim: <?php echo number_format($u['UnitPrice'], 2, ',', '.'); ?> TL</div>
                                </div>
                            </td>
                            <td style="color: #64748b; font-family: monospace;"><?php echo htmlspecialchars($u['SKU']); ?></td>
                            <td style="font-weight: 600;"><?php echo $u['Quantity']; ?></td>
                            <td style="text-align: right; font-weight: bold;"><?php echo number_format($u['SubTotal'], 2, ',', '.'); ?> TL</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8fafc; font-size: 16px;">
                            <td colspan="3" style="text-align: right;"><strong>GENEL TOPLAM</strong></td>
                            <td style="text-align: right; color: #ff6600;"><strong><?php echo number_format($siparis['TotalPrice'], 2, ',', '.'); ?> TL</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const statusSelect = document.getElementById('statusSelect');
        const lojistikPanel = document.getElementById('lojistik-panel');

        function toggleLojistik() {
            if (statusSelect.value === 'shipped') {
                lojistikPanel.style.display = 'block';
            } else {
                lojistikPanel.style.display = 'none';
            }
        }

        window.onload = toggleLojistik;
        statusSelect.onchange = toggleLojistik;
    </script>
</body>
</html>