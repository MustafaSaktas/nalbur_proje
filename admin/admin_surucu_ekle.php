<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) { header("Location: index.php"); exit; }

$mesaj = "";
// Dropdown için kargo firmalarını çekelim
$firmalar = $db->query("SELECT Id, Name FROM shippers ORDER BY Name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // PHP Doğrulaması [cite: 155]
    $sicil = trim($_POST['sicil']);
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $yas = intval($_POST['yas']);
    $firmaId = $_POST['firma_id'];

    if (!empty($sicil) && !empty($ad) && !empty($firmaId)) {
        try {
            // SQL DML: Insert işlemi [cite: 99]
            $kaydet = $db->prepare("INSERT INTO drivers (RegistrationNo, FName, LName, Age, ShipperId) VALUES (?, ?, ?, ?, ?)");
            $kaydet->execute([$sicil, $ad, $soyad, $yas, $firmaId]);
            $mesaj = "<div style='background:#dcfce3; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px;'>Sürücü başarıyla kaydedildi!</div>";
            header("refresh:2; url=admin_suruculer.php");
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
    <title>Yeni Sürücü Ekle - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: #f8fafc;">
    <?php include 'admin_sidebar.php'; ?>
    <main style="margin-left: 260px; padding: 40px;">
        <h2 style="margin-bottom: 25px;">Yeni Sürücü Kaydı</h2>
        <?php echo $mesaj; ?>
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <form action="" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Sicil Numarası</label>
                    <input type="text" name="sicil" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                </div>
                <div style="display:flex; gap:15px; margin-bottom: 15px;">
                    <div style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Ad</label>
                        <input type="text" name="ad" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Soyad</label>
                        <input type="text" name="soyad" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Yaş</label>
                    <input type="number" name="yas" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                </div>
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Bağlı Kargo Firması</label>
                    <select name="firma_id" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                        <option value="">Firma Seçin...</option>
                        <?php foreach($firmalar as $f): ?>
                            <option value="<?php echo $f['Id']; ?>"><?php echo htmlspecialchars($f['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" style="background:#ff6600; color:white; border:none; padding:12px 25px; border-radius:8px; cursor:pointer; font-weight:bold;">Sürücüyü Kaydet</button>
            </form>
        </div>
    </main>
</body>
</html>