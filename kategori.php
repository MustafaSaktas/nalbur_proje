<?php
session_start();
require_once 'db.php'; // Veritabanı bağlantısı ($pdo değişkeni içerdiği varsayılmıştır)

// URL'den gelen kategori ID'sini güvenli bir şekilde alalım (Eğer yoksa 0 yap)
$secilenKatId = isset($_GET['kat']) ? (int)$_GET['kat'] : 0;

// 1. Kategori Adını Veritabanından Çekme (Sayfa başlığı için)
$kategoriAdi = 'TÜM ÜRÜNLER'; // Varsayılan başlık
if ($secilenKatId > 0) {
    $catStmt = $pdo->prepare("SELECT Name FROM Categories WHERE Id = :id AND IsActive = 1");
    $catStmt->execute(['id' => $secilenKatId]);
    $kat = $catStmt->fetch(PDO::FETCH_ASSOC);
    if ($kat) {
        $kategoriAdi = mb_strtoupper($kat['Name'], 'UTF-8');
    }
}

// 2. Akıllı Ürün Çekme Sorgusu (Hem Ana Kategori Hem Alt Kategori İçin)
// Mantık: Ürünün kategorisi seçilen ID'ye eşitse VEYA ürünün kategorisinin üst kategorisi (Parent) seçilen ID'ye eşitse getir.
if ($secilenKatId > 0) {
    $prodStmt = $pdo->prepare("
        SELECT p.Id, p.Name as baslik, p.Price as fiyat, p.ImageUrl as resim 
        FROM Products p
        LEFT JOIN Categories c ON p.CategoryId = c.Id
        WHERE (c.Id = :katId OR c.ParentCategoryId = :katId) 
        AND p.IsActive = 1
        ORDER BY p.CreateAt DESC
    ");
    $prodStmt->execute(['katId' => $secilenKatId]);
} else {
    // Kategori seçilmediyse tüm aktif ürünleri getir
    $prodStmt = $pdo->query("SELECT Id, Name as baslik, Price as fiyat, ImageUrl as resim FROM Products WHERE IsActive = 1 ORDER BY CreateAt DESC");
}

$filtrelenmisUrunler = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>NalburDükkan - <?= htmlspecialchars($kategoriAdi) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-showcase">
        <h2 class="section-title"><?= htmlspecialchars($kategoriAdi) ?> REYONU</h2>
        
        <div class="product-grid">
            <?php if(empty($filtrelenmisUrunler)): ?>
                <div style="text-align:center; width:100%; padding: 50px 0;">
                    <p>Bu kategoride henüz ürün bulunmuyor.</p>
                    <a href="index.php" style="color: #ff6600; text-decoration: underline;">Anasayfaya Dön</a>
                </div>
            <?php else: ?>
                <?php foreach($filtrelenmisUrunler as $urun): ?>
                    <div class="product-card">
                        <a href="urun-detay.php?id=<?= $urun['Id']; ?>">
                            <div class="card-image">
                                <img src="<?= htmlspecialchars($urun['resim']) ?>" alt="<?= htmlspecialchars($urun['baslik']) ?>">
                            </div>
                        </a>
                        
                        <div class="card-details">
                            <a href="urun-detay.php?id=<?= $urun['Id']; ?>" style="text-decoration: none; color: inherit;">
                                <h3 class="product-title"><?= htmlspecialchars($urun['baslik']) ?></h3>
                            </a>
                            
                            <div class="price-container">
                                <span class="new-price"><?= number_format($urun['fiyat'], 2, ',', '.') ?> TL</span>
                            </div>
                            
                            <form action="sepet.php" method="POST">
                                <input type="hidden" name="urun_id" value="<?= $urun['Id']; ?>">
                                <button type="submit" class="add-to-cart-btn">Sepete Ekle</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>