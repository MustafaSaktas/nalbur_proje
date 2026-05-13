<?php
session_start();
// Hata ayıklama modunu açıyoruz (500 hatası yerine gerçek hatayı görmek için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'baglan.php'; // Veritabanı bağlantısı

// 1. SEPETİ BOŞALTMA MANTIĞI
if (isset($_GET['islem']) && $_GET['islem'] == 'sepeti_bosalt') {
    unset($_SESSION['sepet']);
    header("Location: sepet.php");
    exit;
}

// 2. SEPETE ÜRÜN EKLEME VEYA ÇIKARMA MANTIĞI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['urun_id'])) {
    $id = intval($_POST['urun_id']);
    
    // Güvenlik: Önce veritabanında böyle bir ürün var mı kontrol edelim
    $kontrolSorgu = $db->prepare("SELECT Id FROM Products WHERE Id = ? AND IsActive = 1");
    $kontrolSorgu->execute([$id]);
    
    if ($kontrolSorgu->rowCount() > 0) {
        if (!isset($_SESSION['sepet'])) { 
            $_SESSION['sepet'] = array(); 
        }
        
        // Ürün zaten sepette varsa sayısını artır, yoksa 1 olarak ekle
        if (isset($_SESSION['sepet'][$id])) {
            $_SESSION['sepet'][$id]++;
        } else {
            $_SESSION['sepet'][$id] = 1;
        }
        
        // Ürün sepete eklendikten sonra sepet sayfasına yönlendir
        header("Location: sepet.php");
        exit;
    } else {
        echo "<script>alert('Ürün bulunamadı veya pasif!'); window.location.href='index.php';</script>";
        exit;
    }
}

$genelToplam = 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sepetim - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <div style="background: white; padding: 30px; border-radius: 10px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2><i class="fa-solid fa-cart-shopping"></i> Alışveriş Sepetiniz</h2>
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

            <?php if (!empty($_SESSION['sepet'])) { ?>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="text-align: left; background: #f8f9fa;">
                            <th style="padding: 12px;">Ürün</th>
                            <th style="padding: 12px;">Adet</th>
                            <th style="padding: 12px;">Birim Fiyat</th>
                            <th style="padding: 12px;">Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($_SESSION['sepet'] as $id => $adet) { 
                            // Sadece sepetteki ürünlerin bilgisini veritabanından çekiyoruz
                            $urunSorgu = $db->prepare("SELECT Name, Price FROM Products WHERE Id = ?");
                            $urunSorgu->execute([$id]);
                            $urunDetay = $urunSorgu->fetch(PDO::FETCH_ASSOC);

                            if ($urunDetay) {
                                $araToplam = $urunDetay['Price'] * $adet;
                                $genelToplam += $araToplam;
                        ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?php echo htmlspecialchars($urunDetay['Name']); ?></td>
                                <td style="padding: 12px;"><?php echo $adet; ?> Adet</td>
                                <td style="padding: 12px;"><?php echo number_format($urunDetay['Price'], 2, ',', '.'); ?> TL</td>
                                <td style="padding: 12px;"><strong><?php echo number_format($araToplam, 2, ',', '.'); ?> TL</strong></td>
                            </tr>
                        <?php 
                            } 
                        } 
                        ?>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #fff8f5; padding: 20px; border-radius: 8px;">
                    <h3 style="margin: 0;">Genel Toplam: <span style="color: #ff6600; font-size: 24px;"><?php echo number_format($genelToplam, 2, ',', '.'); ?> TL</span></h3>
                    <!-- sepet.php içinde toplam fiyatın altına -->
<div style="text-align: right; margin-top: 20px;">
    <a href="odeme.php" class="filter-btn" style="padding: 15px 30px; text-decoration: none;">
        <i class="fa-solid fa-check-double"></i> Siparişi Tamamla
    </a>
</div>
                    <div>
                        <a href="index.php" style="text-decoration: none; color: #333; padding: 10px 20px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px;">Alışverişe Devam Et</a>
                        <a href="sepet.php?islem=sepeti_bosalt" style="text-decoration: none; color: white; background: #dc3545; padding: 10px 20px; border-radius: 5px;">Sepeti Boşalt</a>
                    </div>
                </div>

            <?php } else { ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="font-size: 18px; color: #666;">Sepetinizde ürün bulunmamaktadır.</p>
                    <a href="index.php" style="color: #ff6600; text-decoration: underline;">Ürünleri incelemek için tıklayın</a>
                </div>
            <?php } ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>