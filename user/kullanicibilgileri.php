

<?php
session_start();
require_once 'db.php';

// Kullanıcı giriş yapmamışsa login'e yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'header.php';

// Oturumdan user_id'yi al
$kullanici_id = $_SESSION['user_id'];

// HATA BURADAN KAYNAKLANIYORDU: $db yerine $pdo kullanılmalı
// Ayrıca tablo adınızı Users ve ID sütununu Id olarak güncelledik.
$sorgu = $pdo->prepare("SELECT * FROM Users WHERE Id = :id");
$sorgu->execute(['id' => $kullanici_id]);
$kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

// Kullanıcı bulunamazsa
if (!$kullanici) {
    die("Kullanıcı bilgileri bulunamadı.");
}
?>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
   .information{
        min-height: 60vh;
        display: flex;
        align-items: center;
        flex-direction: column;
        justify-content: center;
        padding: 40px 15px;
   } 
    .personal-information{
        text-align: center;
        font-size: 35px; /* text-size düzeltildi */
        font-weight: 600; /* text-weight düzeltildi */
        margin-bottom: 20px; /* Form ile başlık arasına boşluk eklendi */

   }

.info form {
        display: flex;
        flex-direction: column;
        gap: 10px; /* Elemanlar arası düzenli boşluk bırakır */
        width: 300px; /* Form genişliği */
   }

   .info input {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
   }
   
   /* Link tasarımlarını buton gibi veya düzenli göstermek için ufak dokunuşlar */
   .action-links {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
   }

   .update {
        margin-top: 15px;
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
   }

</style>



<div class="information">
    <span class="personal-information">KİŞİSEL BİLGİLERİM</span>
    
    <div class="info">
        <form action="guncelle.php" method="POST">
            
            <!-- Veritabanından gelen veriler 'value' içine yazdırılır -->
            <label for="ad">Ad:</label>
            <input type="text" id="ad" name="Ad" value="<?php echo htmlspecialchars($kullanici['FName']); ?>" required> 
            
            <label for="soyad">Soyad:</label>
            <input type="text" id="soyad" name="Soyad" value="<?php echo htmlspecialchars($kullanici['LName']); ?>" required> 
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="Email" value="<?php echo htmlspecialchars($kullanici['Email']); ?>" required> 
            
            <label for="telefon">Telefon:</label>
            <input type="text" id="telefon" name="Telefon" value="<?php echo htmlspecialchars($kullanici['Phone']); ?>" required> 
            
            <div class="action-links">
                <a href="sifre-degistir.php" class="password-change">
                    <div class="action-text-user">
                        <span class="title">Şifre Değişikliği</span>
                    </div>
                </a>
                <a href="adres.php" class="address">
                    <div class="action-text-user">
                        <span class="title">Adres Ekle/Değiştir</span>
                    </div>
                </a>
            </div>

            <button type="submit" class="update">Güncelle</button>
            
        </form>
    </div>
</div>