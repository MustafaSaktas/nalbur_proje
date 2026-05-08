<?php
session_start();
// Hata ayıklama modunu açık tutuyoruz (Pazar günkü teslime kadar faydalı olur)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'urunler.php';
include 'header.php'; // Header içeriği buradan çekiliyor
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NalburDükkan | Profesyonel El Aletleri</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Üst Bar, Header ve Navigasyon Manuel Olarak Buradan Silindi; Çünkü header.php İçinde Mevcut -->

    <!-- SLIDER ALANI -->
    <div class="container"> 
        <div class="hero-slider-container">
            <div class="slider-wrapper">
                <div class="slide"><img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?q=80&w=1200&h=300&auto=format&fit=crop"></div>
                <div class="slide"><img src="https://images.unsplash.com/photo-1581166397057-235af2b3c6dd?q=80&w=1200&h=300&auto=format&fit=crop"></div>
                <div class="slide"><img src="https://images.unsplash.com/photo-1530124566582-a618bc2615dc?q=80&w=1200&h=300&auto=format&fit=crop"></div>
                <div class="slide"><img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?q=80&w=1200&h=300&auto=format&fit=crop"></div>
            </div>
            <button type="button" class="slider-btn prev-btn"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="slider-btn next-btn"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="slider-dots"><span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
        </div>
    </div>
   
    <main class="container product-showcase">
        <h2 class="section-title">İndirimli Ürünler</h2>
        <div class="product-grid">
            <?php foreach($indirimliUrunler as $urun): ?>
            <div class="product-card">
                <div class="badge"><?php echo $urun['etiket']; ?></div>
                <button class="fav-btn"><i class="fa-regular fa-heart"></i></button>
                <div class="card-image"><img src="<?php echo $urun['resim']; ?>" alt="Ürün"></div>
                <div class="card-details">
                    <span class="cargo-badge"><?php echo $urun['kargo']; ?></span>
                    <h3 class="product-title"><?php echo $urun['baslik']; ?></h3>
                    
                    <div class="price-container">
                        <span class="old-price"><?php echo $urun['eski_fiyat']; ?> TL</span>
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
        </div>
    </main>

    <section class="container product-showcase">
        <h2 class="section-title">En Çok Tercih Edilenler</h2>
        <div class="product-grid">
            <?php foreach($cokSatanlar as $urun): ?>
            <div class="product-card">
                <div class="badge" style="background-color: <?php echo isset($urun['renk']) ? $urun['renk'] : '#333'; ?>;"><?php echo $urun['etiket']; ?></div>
                <button class="fav-btn"><i class="fa-regular fa-heart"></i></button>
                <div class="card-image"><img src="<?php echo $urun['resim']; ?>" alt="Ürün"></div>
                <div class="card-details">
                    <span class="cargo-badge">Ücretsiz Kargo</span>
                    <h3 class="product-title"><?php echo $urun['baslik']; ?></h3>
                    <div class="price-container"><span class="price"><?php echo $urun['fiyat']; ?> TL</span></div>
                    <form action="sepet.php" method="POST">
                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                        <input type="hidden" name="islem" value="ekle">
                        <button type="submit" class="add-to-cart-btn">Sepete Ekle</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <script>
        // Slider JS kodların aynen kalıyor...
        const wrapper = document.querySelector('.slider-wrapper');
        const dots = document.querySelectorAll('.dot');
        let slideIndex = 0;
        const totalSlides = 4;
        function showSlide(index) {
            if (index >= totalSlides) slideIndex = 0; 
            if (index < 0) slideIndex = totalSlides - 1; 
            wrapper.style.transform = `translateX(-${slideIndex * 100}%)`;
            dots.forEach(dot => dot.classList.remove('active'));
            dots[slideIndex].classList.add('active');
        }
        document.querySelector('.next-btn').addEventListener('click', () => { slideIndex++; showSlide(slideIndex); });
        document.querySelector('.prev-btn').addEventListener('click', () => { slideIndex--; showSlide(slideIndex); });
        setInterval(() => { slideIndex++; showSlide(slideIndex); }, 5000);
    </script>

<?php include 'footer.php'; ?>

</body>
</html>