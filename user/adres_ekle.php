<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $district = trim($_POST['district']);
    $zip = trim($_POST['zip']);

    $ekle = $db->prepare("INSERT INTO addresses (UserId, Name, Street, City, District, PostalCode) VALUES (?, ?, ?, ?, ?, ?)");
    if ($ekle->execute([$userID, $name, $street, $city, $district, $zip])) {
        header("Location: adreslerim.php?durum=ok");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Adres Ekle - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 500px; margin: 50px auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #334155; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .btn-save { background: #ff6600; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="form-card">
        <h3 style="margin-top: 0; color: #1e293b;">Yeni Adres Ekle</h3>
        <form method="POST">
            <div class="form-group">
                <label>Adres Başlığı (Örn: Evim, İş Yerim)</label>
                <input type="text" name="name" required placeholder="Evim">
            </div>
            <div class="form-group">
                <label>Sokak / Cadde / No</label>
                <input type="text" name="street" required>
            </div>
            <div class="form-group">
                <label>İlçe</label>
                <input type="text" name="district" required>
            </div>
            <div class="form-group">
                <label>Şehir</label>
                <input type="text" name="city" required>
            </div>
            <div class="form-group">
                <label>Posta Kodu</label>
                <input type="text" name="zip">
            </div>
            <button type="submit" class="btn-save">Kaydet</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>