<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'baglan.php';

$mesaj = "";
$tip = ""; // hata veya basari

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $sifre = trim($_POST['sifre']);
    $sifre_tekrar = trim($_POST['sifre_tekrar']);

    // 1. Temel Doğrulamalar
    if (empty($fname) || empty($lname) || empty($email) || empty($sifre)) {
        $mesaj = "Lütfen zorunlu alanları doldurun!";
        $tip = "error";
    } elseif ($sifre != $sifre_tekrar) {
        $mesaj = "Şifreler birbiriyle uyuşmuyor!";
        $tip = "error";
    } else {
        // 2. E-posta Kontrolü (Aynı maille iki kişi kayıt olmasın)
        $kontrol = $db->prepare("SELECT Id FROM users WHERE Email = ?");
        $kontrol->execute([$email]);
        
        if ($kontrol->rowCount() > 0) {
            $mesaj = "Bu e-posta adresi zaten kullanımda!";
            $tip = "error";
        } else {
            // 3. Veritabanına Kayıt (RoleId = 2 olarak kaydediyoruz)
            try {
                $sorgu = $db->prepare("INSERT INTO users (RoleId, FName, LName, Email, Phone, Password_hash, IsActive) VALUES (?, ?, ?, ?, ?, ?, 1)");
                // Not: Profesyonel projelerde password_hash() fonksiyonu kullanılır. 
                // Şimdilik senin login sisteminle uyumlu olması için düz metin kaydediyoruz.
                $sorgu->execute([2, $fname, $lname, $email, $phone, $sifre]);

                $mesaj = "Kaydınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.";
                $tip = "success";
            } catch (PDOException $e) {
                $mesaj = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
                $tip = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Üye Ol | NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .register-box { max-width: 500px; margin: 50px auto; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); border-top: 5px solid #ff6600; }
        .register-box h2 { text-align: center; color: #333; margin-bottom: 25px; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; flex: 1; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-register { width: 100%; padding: 12px; background: #333; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; }
        .btn-register:hover { background: #ff6600; }
        .msg { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .error { background: #f8d7da; color: #d9534f; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body style="background: #f4f4f4;">
    <?php include 'header.php'; ?>

    <div class="register-box">
        <h2>Yeni Üyelik Oluştur</h2>
        
        <?php if($mesaj): ?>
            <div class="msg <?php echo $tip; ?>"><?php echo $mesaj; ?></div>
        <?php endif; ?>

        <form action="kayit.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Adınız *</label>
                    <input type="text" name="fname" required>
                </div>
                <div class="form-group">
                    <label>Soyadınız *</label>
                    <input type="text" name="lname" required>
                </div>
            </div>

            <div class="form-group">
                <label>E-posta Adresi *</label>
                <input type="email" name="email" required placeholder="ornek@mail.com">
            </div>

            <div class="form-group">
                <label>Telefon Numarası</label>
                <input type="text" name="phone" placeholder="05xx xxx xx xx">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Şifre *</label>
                    <input type="password" name="sifre" required>
                </div>
                <div class="form-group">
                    <label>Şifre Tekrar *</label>
                    <input type="password" name="sifre_tekrar" required>
                </div>
            </div>

            <button type="submit" class="btn-register">Üye Ol</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            Zaten hesabınız var mı? <a href="giris.php" style="color: #ff6600; font-weight: bold;">Giriş Yap</a>
        </p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>