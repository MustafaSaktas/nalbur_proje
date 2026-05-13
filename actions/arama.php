<?php
session_start();
// Hata ayıklama modunu açıyoruz (Teslime kadar kritik)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'baglan.php'; // Veritabanı bağlantısı şart

// 1. Kullanıcının arama terimini alıyoruz ve temizliyoruz
$aramaTerimi = isset($_GET['q']) ? trim($_GET['q']) : '';
$aramaSonuclari = [];

if (!empty($aramaTerimi)) {
    // 2. MySQL'e "İsminde veya Açıklamasında bu kelime geçen ürünleri getir" diyoruz
    // LIKE %terim% yapısı, kelimenin nerede geçtiğinin fark etmeksizin bulur.
    $sorguMetni = "SELECT * FROM Products WHERE (Name LIKE ? OR Description LIKE ?) AND IsActive = 1";
    $sorgu = $db->prepare($sorguMetni);
    $sorgu->execute(["%$aramaTerimi%", "%$aramaTerimi%"]);
    $aramaSonuclari = $sorgu->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arama Sonuçları - <?php echo htmlspecialchars($aramaTerimi); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-showcase">
        <h2 class="section-title">
            <?php echo !empty($aramaTerimi) ? '"' . htmlspecialchars($aramaTerimi) . '" İÇİN SONUÇLAR' : 'TÜM ÜRÜNLER'; ?>
        </h2>
        
        <div class="product-grid">
            <?php if(empty($aramaSonuclari)): ?>
                <div style="text-align:center; width:100%; padding: 50px 0;">
                    <p style="font-size: 18px; color: #666;">Aradığınız kriterlere uygun bir ürün bulamadık.</p>
                    <a href="index.php" style="color: #ff6600; text-decoration: underline; font-weight: bold;">Anasayfaya Dön ve Ürünlere Göz At</a>
                </div>
            <?php else: ?>
                <?php foreach($aramaSonuclari as $urun): ?>
                    <div class="product-card">
                        <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>">
                            <div class="card-image">
                                <img src="<?php echo $urun['ImagePath']; ?>" alt="Ürün Görseli">
                            </div>
                        </a>
                        
                        <div class="card-details">
                            <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>" style="text-decoration:none; color:inherit;">
                                <h3 class="product-title"><?php echo $urun['Name']; ?></h3>
                            </a>
                            
                            <div class="price-container">
                                <span class="new-price"><?php echo number_format($urun['Price'], 2, ',', '.'); ?> TL</span>
                            </div>
                            
                            <form action="sepet.php" method="POST">
                                <input type="hidden" name="urun_id" value="<?php echo $urun['Id']; ?>">
                                <button type="submit" class="add-to-cart-btn">Sepete Ekle</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>