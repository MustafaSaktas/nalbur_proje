<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$kullanici_id = $_SESSION['user_id'];
$hata   = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mevcut_sifre = $_POST['mevcut_sifre'];
    $yeni_sifre   = $_POST['yeni_sifre'];
    $yeni_tekrar  = $_POST['yeni_tekrar'];

    // Mevcut şifreyi veritabanından çek
    $stmt = $pdo->prepare("SELECT Password_hash FROM Users WHERE Id = ?");
    $stmt->execute([$kullanici_id]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($mevcut_sifre, $kullanici['Password_hash'])) {
        $hata = 'Mevcut şifreniz hatalı.';

    } elseif (strlen($yeni_sifre) < 6) {
        $hata = 'Yeni şifre en az 6 karakter olmalıdır.';

    } elseif ($yeni_sifre !== $yeni_tekrar) {
        $hata = 'Yeni şifreler eşleşmiyor.';

    } elseif ($mevcut_sifre === $yeni_sifre) {
        $hata = 'Yeni şifre mevcut şifreyle aynı olamaz.';

    } else {
        $hash = password_hash($yeni_sifre, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE Users SET Password_hash = ? WHERE Id = ?");
        $stmt->execute([$hash, $kullanici_id]);
        $basari = 'Şifreniz başarıyla değiştirildi.';
    }
}

require_once 'header.php';
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* guncelle.php ile aynı stiller */
.guncelle-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 15px;
}
.guncelle-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 40px;
    width: 100%;
    max-width: 460px;
}
.guncelle-card h2 {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    text-align: center;
}
.guncelle-card label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
    display: block;
}
.guncelle-card input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.3s;
}
.guncelle-card input:focus { border-color: #e11d61; }
.guncelle-btn {
    width: 100%;
    background: #333;
    color: white;
    border: none;
    padding: 13px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}
.guncelle-btn:hover { background: #e11d61; }
.error-msg {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 15px;
}
.success-msg {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 15px;
}
.back-link { text-align: center; margin-top: 15px; font-size: 14px; }
.back-link a { color: #e11d61; font-weight: 600; }
</style>

<div class="guncelle-container">
    <div class="guncelle-card">
        <h2><i class="fa-solid fa-lock"></i> Şifre Değiştir</h2>

        <?php if ($hata): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($hata) ?>
            </div>
        <?php endif; ?>

        <?php if ($basari): ?>
            <div class="success-msg">
                <i class="fa-solid fa-circle-check"></i> <?= $basari ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Mevcut Şifre</label>
            <input type="password" name="mevcut_sifre"
                   placeholder="Mevcut şifreniz" required>

            <label>Yeni Şifre</label>
            <input type="password" name="yeni_sifre"
                   placeholder="En az 6 karakter" required>

            <label>Yeni Şifre Tekrar</label>
            <input type="password" name="yeni_tekrar"
                   placeholder="Yeni şifrenizi tekrar girin" required>

            <button type="submit" class="guncelle-btn">
                <i class="fa-solid fa-key"></i> Şifremi Değiştir
            </button>
        </form>

        <div class="back-link">
            <a href="kullanicibilgileri.php">← Bilgilerime Dön</a>
        </div>
    </div>
</div>