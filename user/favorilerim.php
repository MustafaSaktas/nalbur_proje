<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

$u_id = $_SESSION['user_id'];

// Favori ürünleri JOIN ile tek sorguda çekiyoruz
$sorgu = $db->prepare("
    SELECT p.* FROM Products p 
    JOIN Favorites f ON p.Id = f.ProductId 
    WHERE f.UserId = ?
");
$sorgu->execute([$u_id]);
$favoriler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Favorilerim | NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-showcase">
        <h2 class="section-title"><i class="fa-solid fa-heart" style="color: #ff6600;"></i> Favori Ürünlerim</h2>
        
        <div class="product-grid">
            <?php if (empty($favoriler)): ?>
                <div style="text-align:center; width:100%; padding: 50px 0;">
                    <p>Henüz favori ürününüz bulunmuyor.</p>
                    <a href="index.php" style="color: #ff6600; text-decoration: underline;">Ürünleri keşfetmeye başla</a>
                </div>
            <?php else: ?>
                <?php foreach($favoriler as $urun): ?>
                    <div class="product-card">
                        <a href="favori_islem.php?id=<?php echo $urun['Id']; ?>" class="fav-btn active" style="color: #ff6600;">
                            <i class="fa-solid fa-heart"></i>
                        </a>
                        <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>">
                            <div class="card-image"><img src="<?php echo $urun['ImagePath']; ?>" alt="Ürün"></div>
                        </a>
                        <div class="card-details">
                            <h3 class="product-title"><?php echo $urun['Name']; ?></h3>
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