<?php
session_start();
include 'urunler.php';

// 1. URL'den ID'yi yakalıyoruz
$urunID = isset($_GET['id']) ? $_GET['id'] : 0;

// 2. Tüm havuzda bu ID'li ürünü buluyoruz
$tumUrunler = array_merge($indirimliUrunler, $cokSatanlar, $kategoriUrunleri);
$secilenUrun = null;

foreach ($tumUrunler as $u) {
    if ($u['id'] == $urunID) {
        $secilenUrun = $u;
        break;
    }
}

// Ürün bulunamazsa ana sayfaya gönder
if (!$secilenUrun) { header("Location: index.php"); exit; }
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $secilenUrun['baslik']; ?> - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container product-detail-wrapper" style="display: flex; gap: 50px; margin-top: 50px; background: white; padding: 30px; border-radius: 15px;">
        <div class="detail-left" style="flex: 1;">
            <img src="<?php echo $secilenUrun['resim']; ?>" style="width: 100%; border-radius: 10px;">
        </div>
        
        <div class="detail-right" style="flex: 1;">
            <h1 style="color: #333;"><?php echo $secilenUrun['baslik']; ?></h1>
            <p style="color: #ff6600; font-size: 32px; font-weight: bold; margin: 20px 0;">
                <?php echo number_format($secilenUrun['fiyat'], 2); ?> TL
            </p>
            <div style="background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="margin-bottom: 10px;">Ürün Açıklaması</h4>
                <p style="line-height: 1.6; color: #666;"><?php echo $secilenUrun['aciklama']; ?></p>
            </div>

            <!-- urun-detay.php içinde açıklama kısmının hemen altına -->
<div class="product-specs-container" style="margin-top: 20px;">
    <h4 style="margin-bottom: 15px;">Teknik Özellikler</h4>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <?php 
        // Eğer ürünün özellikleri tanımlıysa döngüye gir
        if (!empty($secilenUrun['ozellikler'])): 
            foreach ($secilenUrun['ozellikler'] as $ozellik): 
        ?>
          <div class="spec-box" style="
    background: #fff5f0; 
    border: 1.5px solid #ff6600; 
    padding: 10px 18px; 
    border-radius: 8px; 
    font-size: 14px; 
    color: #222; 
    font-weight: 600; 
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 5px rgba(255, 102, 0, 0.1); 
">
    <i class="fa-solid fa-square-check" style="color: #ff6600; font-size: 14px;"></i>
    <?php echo $ozellik; ?>
</div>
        <?php 
            endforeach; 
        else:
            echo "<p style='font-size: 13px; color: #999;'>Bu ürün için teknik özellik belirtilmemiştir.</p>";
        endif; 
        ?>
    </div>
</div>

            <form action="sepet.php" method="POST">
                <input type="hidden" name="urun_id" value="<?php echo $secilenUrun['id']; ?>">
                <button type="submit" style="background: #ff6600; color: white; border: none; padding: 15px 40px; border-radius: 5px; cursor: pointer; font-size: 18px; margin-top: 30px;">
                    Sepete Ekle
                </button>
            </form>
        </div>
    </main>

<?php include 'footer.php'; ?>

</body>
</html>