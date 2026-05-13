<?php
session_start();
include 'baglan.php';

// Güvenlik: Kullanıcı giriş yapmamışsa girişe yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

$userId = $_SESSION['user_id'];
$mesaj = "";

// 1. ŞİFRE DEĞİŞTİRME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sifre_degistir'])) {
    $eski_sifre = trim($_POST['eski_sifre']);
    $yeni_sifre = $_POST['yeni_sifre'];
    $yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'];

    // Kullanıcının mevcut şifre hash'ini veritabanından çek
    $st = $db->prepare("SELECT Password_hash FROM users WHERE Id = ?");
    $st->execute([$userId]);
    $user = $st->fetch();
    $db_sifre = $user['Password_hash'];

    // Eski şifre doğru mu? (Modern Hash VEYA Düz Metin VEYA MD5 VEYA SHA1)
    if (password_verify($eski_sifre, $db_sifre) || $eski_sifre === $db_sifre || md5($eski_sifre) === $db_sifre || sha1($eski_sifre) === $db_sifre) {
        
        // Yeni şifreler birbiriyle eşleşiyor mu?
        if ($yeni_sifre === $yeni_sifre_tekrar) {
            
            // Şifre uzunluk kontrolü
            if (strlen($yeni_sifre) >= 6) {
                
                // Yeni şifreyi güvenli bir şekilde hash'le
                $yeni_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                $guncelle = $db->prepare("UPDATE users SET Password_hash = ? WHERE Id = ?");
                
                if ($guncelle->execute([$yeni_hash, $userId])) {
                    $mesaj = "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> Şifreniz başarıyla güncellendi!</div>";
                } else {
                    $mesaj = "<div class='alert-danger'>Şifre güncellenirken veritabanı hatası oluştu.</div>";
                }
            } else {
                 $mesaj = "<div class='alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Yeni şifreniz en az 6 karakter olmalıdır.</div>";
            }
        } else {
            $mesaj = "<div class='alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Yeni şifreler birbiriyle eşleşmiyor!</div>";
        }
    } else {
        $mesaj = "<div class='alert-danger'><i class='fa-solid fa-circle-xmark'></i> Mevcut şifrenizi yanlış girdiniz!</div>";
    }
}

// 2. TEMEL BİLGİ GÜNCELLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bilgi_guncelle'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $phone = trim($_POST['phone']);

    $guncelle = $db->prepare("UPDATE users SET FName = ?, LName = ?, Phone = ? WHERE Id = ?");
    if ($guncelle->execute([$fname, $lname, $phone, $userId])) {
        // Sağ üstteki 'Merhaba, İsim' kısmının anında güncellenmesi için Session'ı da tazeliyoruz
        $_SESSION['user_name'] = $fname . ' ' . $lname; 
        $mesaj = "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> Profil bilgileriniz güncellendi!</div>";
    }
}

// Kullanıcının en güncel bilgilerini veritabanından çek (Formlara yazdırmak için)
$st = $db->prepare("SELECT * FROM users WHERE Id = ?");
$st->execute([$userId]);
$kullanici = $st->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profil Ayarları - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        .profil-container { max-width: 800px; margin: 40px auto; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card h3 { margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; color: #1e293b; font-size: 18px;}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #475569; font-size: 14px;}
        .form-group input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box; font-family: inherit;}
        .btn-update { background: #ff6600; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; transition: 0.3s;}
        .btn-update:hover { background: #e65c00; }
        .btn-dark { background: #1e293b; }
        .btn-dark:hover { background: #0f172a; }
        .alert-success { background: #dcfce3; color: #166534; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #991b1b; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <div class="profil-container">
            <h2 style="color: #1e293b; margin-bottom: 20px;"><i class="fa-solid fa-gear" style="color: #ff6600;"></i> Profil Ayarları</h2>

            <?php echo $mesaj; ?>

            <!-- Kişisel Bilgiler Formu -->
            <div class="card">
                <h3><i class="fa-regular fa-id-card"></i> Kişisel Bilgiler</h3>
                <form method="POST">
                    <input type="hidden" name="bilgi_guncelle" value="1">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Adınız</label>
                            <input type="text" name="fname" value="<?php echo htmlspecialchars($kullanici['FName']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Soyadınız</label>
                            <input type="text" name="lname" value="<?php echo htmlspecialchars($kullanici['LName']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>E-posta Adresi (Değiştirilemez)</label>
                        <input type="email" value="<?php echo htmlspecialchars($kullanici['Email']); ?>" disabled style="background: #f1f5f9; cursor: not-allowed; color: #94a3b8;">
                    </div>
                    
                    <div class="form-group">
                        <label>Telefon Numarası</label>
                        <input type="text" name="phone" placeholder="05XX XXX XX XX" value="<?php echo htmlspecialchars($kullanici['Phone'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn-update">Bilgileri Kaydet</button>
                </form>
            </div>

            <!-- Şifre Değiştirme Formu -->
            <div class="card">
                <h3><i class="fa-solid fa-lock"></i> Şifre Değiştir</h3>
                <form method="POST">
                    <input type="hidden" name="sifre_degistir" value="1">
                    <div class="form-group">
                        <label>Mevcut Şifreniz</label>
                        <input type="password" name="eski_sifre" required placeholder="Şu anki şifrenizi girin">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Yeni Şifre</label>
                            <input type="password" name="yeni_sifre" required placeholder="En az 6 karakter">
                        </div>
                        <div class="form-group">
                            <label>Yeni Şifre (Tekrar)</label>
                            <input type="password" name="yeni_sifre_tekrar" required placeholder="Yeni şifrenizi tekrar girin">
                        </div>
                    </div>
                    <button type="submit" class="btn-update btn-dark">Şifreyi Güncelle</button>
                </form>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>