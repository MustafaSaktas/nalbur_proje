<?php
session_start();
include 'urunler.php';

$secilenKat = isset($_GET['kat']) ? $_GET['kat'] : '';

// 1. ANA KATEGORİ HARİTASI (Mapping)
// Bu dizi, ana başlıkların hangi alt kategorileri kapsadığını belirler.
$anaKategoriler = [
    "el-aletleri"        => ["cekic-balyoz", "tornavida", "pense", "olcum"],
    "elektrikli-aletler" => ["matkap", "taslama", "kaynak", "testere"],
    "bahce-tarim"        => ["sulama", "kesme", "cim", "ilaclama"],
    "hirdavat"           => ["vida", "kilit", "mentese", "tesisat"],
    "boya-yapi"          => ["boya", "dis-boya", "firca", "yapistirici"],
    "is-guvenligi"       => ["baret", "eldiven", "ayakkabi", "maske", "gozluk"]
];

// 2. Ürün Havuzunu Birleştirme
$tumUrunHavuzu = array_merge($indirimliUrunler, $cokSatanlar, $kategoriUrunleri);

// 3. Akıllı Filtreleme Mantığı
$filtrelenmisUrunler = array_filter($tumUrunHavuzu, function($urun) use ($secilenKat, $anaKategoriler) {
    if (!isset($urun['kat'])) return false;

    // DURUM A: Eğer URL'den gelen kategori bir ANA KATEGORİ ise (Örn: hirdavat)
    if (isset($anaKategoriler[$secilenKat])) {
        // Ürünün alt kategorisi, bu ana kategorinin listesinde var mı?
        return in_array($urun['kat'], $anaKategoriler[$secilenKat]);
    }

    // DURUM B: Eğer URL'den gelen kategori direkt bir ALT KATEGORİ ise (Örn: vida)
    return $urun['kat'] === $secilenKat;
});

// Filtrelemeden sonra indexleri sıfırlamak (opsiyonel ama düzenli kod için iyidir)
$filtrelenmisUrunler = array_values($filtrelenmisUrunler);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>NalburDükkan - <?php echo ucfirst($secilenKat); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-showcase">
        <h2 class="section-title"><?php echo strtoupper(str_replace('-', ' ', $secilenKat)); ?> REYONU</h2>
        
        <div class="product-grid">
            <?php if(empty($filtrelenmisUrunler)): ?>
                <div style="text-align:center; width:100%; padding: 50px 0;">
                    <p>Bu kategoride henüz ürün bulunmuyor.</p>
                    <a href="index.php" style="color: #ff6600; text-decoration: underline;">Anasayfaya Dön</a>
                </div>
            <?php else: ?>
                <?php foreach($filtrelenmisUrunler as $urun): ?>
                    <div class="product-card">
    <!-- Resme tıklandığında detay sayfasına git -->
    <a href="urun-detay.php?id=<?php echo $urun['id']; ?>">
        <div class="card-image"><img src="<?php echo $urun['resim']; ?>" alt="Ürün"></div>
    </a>
    
    <div class="card-details">
        <!-- Başlığa tıklandığında detay sayfasına git -->
        <a href="urun-detay.php?id=<?php echo $urun['id']; ?>" style="text-decoration: none; color: inherit;">
            <h3 class="product-title"><?php echo $urun['baslik']; ?></h3>
        </a>
        
        <div class="price-container">
            <span class="new-price"><?php echo $urun['fiyat']; ?> TL</span>
        </div>
        
        <!-- Sepete ekle butonu form olarak kalmaya devam eder -->
        <form action="sepet.php" method="POST">
            <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
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