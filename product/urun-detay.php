<?php
session_start();
include 'baglan.php';

$urunID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userID = $_SESSION['user_id'] ?? 0;

// 1. Ürün Bilgilerini ve Stok Miktarını Çekiyoruz
$sorgu = $db->prepare("
    SELECT p.*, s.Quantity as StockCount 
    FROM products p 
    LEFT JOIN stocks s ON p.Id = s.ProductId 
    WHERE p.Id = ? AND p.IsActive = 1
");
$sorgu->execute([$urunID]);
$urun = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$urun) { header("Location: index.php"); exit; }

// 2. Teknik Özellikler (Sütun ismini FeatureText olarak kesinleştirdik)
$ozellikSorgu = $db->prepare("SELECT FeatureText FROM productfeatures WHERE ProductId = ?");
$ozellikSorgu->execute([$urunID]);
$ozellikler = $ozellikSorgu->fetchAll(PDO::FETCH_COLUMN);

// 3. Favori Durumu
$isFavorite = false;
if ($userID > 0) {
    $favSorgu = $db->prepare("SELECT Id FROM favorites WHERE UserId = ? AND ProductId = ?");
    $favSorgu->execute([$userID, $urunID]);
    $isFavorite = $favSorgu->fetch() ? true : false;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($urun['Name']); ?> - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .favorite-btn { background: none; border: 1px solid #ddd; padding: 10px; border-radius: 50%; cursor: pointer; transition: 0.3s; color: #999; }
        .favorite-btn.active { color: #e11d48; border-color: #e11d48; background: #fff1f2; }
        .stock-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; margin-bottom: 10px; }
        .stock-in { background: #dcfce3; color: #166534; }
        .stock-out { background: #fee2e2; color: #991b1b; }
        
        /* Teknik Özellik Rozetleri */
        .spec-badge {
            background: #f0fdf4; 
            border: 1px solid #dcfce3; 
            padding: 10px 16px; 
            border-radius: 12px; 
            font-size: 14px; 
            color: #166534; 
            font-weight: 600; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: 0.3s;
        }
        .spec-badge:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-detail-wrapper" style="display: flex; gap: 50px; margin-top: 50px; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        
        <div class="detail-left" style="flex: 1; text-align: center;">
            <img src="<?php echo $urun['ImagePath']; ?>" style="max-width: 100%; height: auto; border-radius: 12px; border: 1px solid #f1f5f9;" alt="<?php echo htmlspecialchars($urun['Name']); ?>">
        </div>
        
        <div class="detail-right" style="flex: 1;">
            <?php if($urun['StockCount'] > 0): ?>
                <span class="stock-badge stock-in"><i class="fa-solid fa-check"></i> Stokta Var (<?php echo $urun['StockCount']; ?> Adet)</span>
            <?php else: ?>
                <span class="stock-badge stock-out"><i class="fa-solid fa-xmark"></i> Stok Tükendi</span>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: start;">
                <h1 style="color: #1e293b; margin: 0;"><?php echo htmlspecialchars($urun['Name']); ?></h1>
                <button class="favorite-btn <?php echo $isFavorite ? 'active' : ''; ?>">
                    <i class="<?php echo $isFavorite ? 'fa-solid' : 'fa-regular'; ?> fa-heart fa-xl"></i>
                </button>
            </div>

            <p style="color: #ff6600; font-size: 36px; font-weight: 800; margin: 15px 0;">
                <?php echo number_format($urun['Price'], 2, ',', '.'); ?> TL
            </p>
            
            <div style="border-top: 1px solid #f1f5f9; padding-top: 20px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 10px; color: #475569;"><i class="fa-solid fa-circle-info"></i> Ürün Açıklaması</h4>
                <p style="line-height: 1.7; color: #64748b; font-size: 15px;">
                    <?php echo !empty($urun['Description']) ? nl2br(htmlspecialchars($urun['Description'])) : 'Bu ürün için detaylı açıklama girilmemiştir.'; ?>
                </p>
            </div>

            <div class="product-specs-container">
                <h4 style="margin-bottom: 15px; color: #475569;"><i class="fa-solid fa-list-check" style="color: #ff6600;"></i> Teknik Özellikler</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    <?php if (!empty($ozellikler)): ?>
                        <?php foreach ($ozellikler as $ozellik): ?>
                            <div class="spec-badge">
                                <i class="fa-solid fa-circle-check" style="color: #22c55e;"></i>
                                <?php echo htmlspecialchars($ozellik); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style='font-size: 13px; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 15px; width: 100%; border-radius: 8px; text-align: center;'>
                            Bu ürün için teknik özellik belirtilmemiş.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <form action="sepet.php" method="POST" style="margin-top: 35px;">
                <input type="hidden" name="urun_id" value="<?php echo $urun['Id']; ?>">
                <button type="submit" <?php echo ($urun['StockCount'] <= 0) ? 'disabled style="background: #cbd5e1; cursor: not-allowed;"' : ''; ?> 
                        style="background: #ff6600; color: white; border: none; padding: 18px 50px; border-radius: 10px; cursor: pointer; font-size: 18px; font-weight: bold; width: 100%; transition: 0.3s;">
                    <i class="fa-solid fa-cart-shopping"></i> <?php echo ($urun['StockCount'] > 0) ? 'Sepete Ekle' : 'Stok Tükendi'; ?>
                </button>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>