<?php
session_start();
include 'urunler.php';

// 1. Kullanıcının arama terimini alıyoruz
$sorgu = isset($_GET['q']) ? trim($_GET['q']) : '';

// 2. Tüm ürünleri tek bir havuzda birleştiriyoruz (Kategori sayfasındaki mantığın aynısı)
$tumUrunHavuzu = array_merge($indirimliUrunler, $cokSatanlar, $kategoriUrunleri);

// 3. Arama Filtrelemesi
$aramaSonuclari = [];
if (!empty($sorgu)) {
    $aramaSonuclari = array_filter($tumUrunHavuzu, function($urun) use ($sorgu) {
        // Hem ürün başlığında hem de kategorisinde arama yapıyoruz
        // stripos() büyük/küçük harf duyarsız arama yapar (Case-insensitive)
        return (stripos($urun['baslik'], $sorgu) !== false) || 
               (stripos($urun['kat'], $sorgu) !== false);
    });
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Arama Sonuçları - <?php echo htmlspecialchars($sorgu); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-showcase">
        <h2 class="section-title">"<?php echo htmlspecialchars($sorgu); ?>" İÇİN SONUÇLAR</h2>
        
        <div class="product-grid">
            <?php if(empty($aramaSonuclari)): ?>
                <div style="text-align:center; width:100%; padding: 50px 0;">
                    <p>Aradığınız kriterlere uygun ürün bulunamadı.</p>
                    <a href="index.php" style="color: #ff6600; text-decoration: underline;">Tüm ürünlere göz atın</a>
                </div>
            <?php else: ?>
                <?php foreach($aramaSonuclari as $urun): ?>
                    <div class="product-card">
                        <div class="card-image"><img src="<?php echo $urun['resim']; ?>" alt="Ürün"></div>
                        <div class="card-details">
                            <h3 class="product-title"><?php echo $urun['baslik']; ?></h3>
                            <div class="price-container">
                                <span class="new-price"><?php echo $urun['fiyat']; ?> TL</span>
                            </div>
                            <form action="sepet.php" method="POST">
                                <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                                <input type="hidden" name="islem" value="ekle">
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