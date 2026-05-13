<?php
session_start();
include 'baglan.php';

// Sepet boşsa veya giriş yapılmamışsa anasayfaya at
if (empty($_SESSION['sepet']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// 1. Kullanıcının Temel Bilgilerini Çek (Ad Soyad için)
$userSt = $db->prepare("SELECT FName, LName, Phone FROM users WHERE Id = ?");
$userSt->execute([$userId]);
$user = $userSt->fetch();
$kayitliAd = $user['FName'] . ' ' . $user['LName'];

// 2. VARSAYILAN ADRESİ ÇEK (addresses tablosundan)
$addrSt = $db->prepare("SELECT * FROM addresses WHERE UserId = ? AND IsDefault = 1 LIMIT 1");
$addrSt->execute([$userId]);
$varsayilanAdres = $addrSt->fetch();

// Eğer varsayılan adres varsa, textarea için birleştirilmiş metin oluşturalım
$hazirAdresMetni = "";
if ($varsayilanAdres) {
    $hazirAdresMetni = $varsayilanAdres['Street'] . " " . $varsayilanAdres['District'] . " / " . $varsayilanAdres['City'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ödeme Yap - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container" style="margin: 50px auto; max-width: 600px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.1);">
        <h2 style="color: #ff6600; text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 25px;">
            <i class="fa-solid fa-truck-fast"></i> Teslimat ve Ödeme Bilgileri
        </h2>

        <?php if($varsayilanAdres): ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 8px; margin-bottom: 20px; color: #166534; font-size: 14px;">
                <i class="fa-solid fa-circle-check"></i> <strong>Varsayılan adresiniz otomatik dolduruldu.</strong> 
                <a href="adreslerim.php" style="color: #166534; text-decoration: underline; margin-left: 5px;">Değiştir</a>
            </div>
        <?php else: ?>
            <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 10px; border-radius: 8px; margin-bottom: 20px; color: #9a3412; font-size: 14px;">
                <i class="fa-solid fa-circle-info"></i> Kayıtlı adresiniz bulunamadı. Lütfen bilgileri giriniz.
            </div>
        <?php endif; ?>
        
        <form action="siparis_ver.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #475569;">Alıcı Ad Soyad:</label>
                <input type="text" name="alici_ad" value="<?php echo htmlspecialchars($kayitliAd); ?>" required 
                       style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #475569;">Telefon Numarası:</label>
                <input type="text" name="telefon" value="<?php echo htmlspecialchars($user['Phone'] ?? ''); ?>" required placeholder="05XX XXX XX XX" 
                       style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #475569;">Teslimat Adresi:</label>
                <textarea name="adres" required placeholder="Mahalle, Sokak, No, İlçe/İl" 
                          style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; height: 100px; box-sizing: border-box; font-family: inherit;"><?php echo htmlspecialchars($hazirAdresMetni); ?></textarea>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; color: #475569;">Ödeme Yöntemi:</label>
                <select name="odeme_yontemi" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; background: white;">
                    <option value="Kapıda Nakit Ödeme">Kapıda Nakit Ödeme</option>
                    <option value="Kapıda Kredi Kartı (POS)">Kapıda Kredi Kartı (POS)</option>
                </select>
            </div>
            
            <button type="submit" style="width: 100%; background: #22c55e; color: white; border: none; padding: 18px; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                <i class="fa-solid fa-cart-check"></i> Siparişi Tamamla
            </button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>