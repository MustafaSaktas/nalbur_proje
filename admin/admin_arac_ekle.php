<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) { header("Location: index.php"); exit; }

$mesaj = "";
// Firmaları çekelim (Dropdown için)
$firmalar = $db->query("SELECT Id, Name FROM shippers ORDER BY Name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plaka = trim($_POST['plaka']);
    $marka = trim($_POST['marka']);
    $model = trim($_POST['model']);
    $firmaId = $_POST['firma_id'];

    if (!empty($plaka) && !empty($firmaId)) {
        try {
            // SQL DML: Insert işlemi [cite: 99]
            $kaydet = $db->prepare("INSERT INTO vehicles (PlateNumber, Brand, Model, ShipperId) VALUES (?, ?, ?, ?)");
            $kaydet->execute([$plaka, $marka, $model, $firmaId]);
            $mesaj = "<div style='background:#dcfce3; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px;'>Araç başarıyla kaydedildi!</div>";
            header("refresh:2; url=admin_araclar.php");
        } catch (PDOException $e) {
            $mesaj = "<div style='background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px;'>Hata: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Araç Ekle - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; }
        .form-card { background: white; padding: 30px; border-radius: 12px; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #475569; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .btn-submit { background: #ff6600; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <main class="admin-container">
        <h2>Yeni Araç Kaydı</h2>
        <?php echo $mesaj; ?>
        <div class="form-card">
            <form action="" method="POST">
                <div class="form-group">
                    <label>Plaka Numarası</label>
                    <input type="text" name="plaka" placeholder="Örn: 78 AB 123" required>
                </div>
                <div class="form-group">
                    <label>Marka</label>
                    <input type="text" name="marka" placeholder="Örn: Ford">
                </div>
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" placeholder="Örn: Transit">
                </div>
                <div class="form-group">
                    <label>Bağlı Kargo Firması</label>
                    <select name="firma_id" required>
                        <option value="">Firma Seçin...</option>
                        <?php foreach($firmalar as $f): ?>
                            <option value="<?php echo $f['Id']; ?>"><?php echo htmlspecialchars($f['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Aracı Sisteme Kaydet</button>
            </form>
        </div>
    </main>
</body>
</html>