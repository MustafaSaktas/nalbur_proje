<!-- header.php -->
<header class="main-header">
    <div class="container header-wrapper">
        <div class="logo">
            <a href="index.php"><i class="fa-solid fa-toolbox" style="color: #ff6600;"></i> <span>NalburDükkan</span></a>
        </div>
        <!-- header.php içindeki search-bar kısmını şu şekilde revize et -->
<form action="arama.php" method="GET" class="search-bar">
    <input type="text" name="q" placeholder="Ürün, kategori veya marka ara..." required>
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</form>
        <div class="user-actions">
            <a href="#" class="action-item">
                <i class="fa-regular fa-user"></i>
                <div class="action-text"><span class="title">Giriş Yap</span><span class="sub">veya Üye Ol</span></div>
            </a>
            <a href="sepet.php" class="action-item cart-action">
                <div class="cart-icon-wrapper">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge"><?php echo isset($_SESSION['sepet']) ? array_sum($_SESSION['sepet']) : 0; ?></span>
                </div>
                <div class="action-text"><span class="title">Sepetim</span></div>
            </a>
        </div>
    </div>
</header>

<nav class="category-nav">
    <div class="container">
        <ul>
            <!-- 1. EL ALETLERİ -->
            <li class="dropdown">
                <a href="kategori.php?kat=el-aletleri"><i class="fa-solid fa-wrench"></i> El Aletleri</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=cekic-balyoz">Çekiçler & Balyozlar</a>
                    <a href="kategori.php?kat=tornavida">Tornavida Setleri</a>
                    <a href="kategori.php?kat=pense">Penseler & Anahtarlar</a>
                    <a href="kategori.php?kat=olcum">Ölçüm Aletleri</a>
                </div>
            </li>

            <!-- 2. ELEKTRİKLİ ALETLER -->
            <li class="dropdown">
                <a href="kategori.php?kat=elektrikli-aletler"><i class="fa-solid fa-screwdriver-wrench"></i> Elektrikli Aletler</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=matkap">Matkaplar</a>
                    <a href="kategori.php?kat=taslama">Taşlama Makinaları</a>
                    <a href="kategori.php?kat=kaynak">Kaynak Makinaları</a>
                    <a href="kategori.php?kat=testere">Elektrikli Testereler</a>
                </div>
            </li>

            <!-- 3. BAHÇE & TARIM -->
            <li class="dropdown">
                <a href="kategori.php?kat=bahce-tarim"><i class="fa-solid fa-leaf"></i> Bahçe & Tarım</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=sulama">Sulama Sistemleri</a>
                    <a href="kategori.php?kat=kesme">Budama Makasları</a>
                    <a href="kategori.php?kat=cim">Çim Biçme Makineleri</a>
                    <a href="kategori.php?kat=ilaclama">İlaçlama Pompaları</a>
                </div>
            </li>

            <!-- 4. HIRDAVAT -->
            <li class="dropdown">
                <a href="kategori.php?kat=hirdavat"><i class="fa-solid fa-toolbox"></i> Hırdavat</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=vida">Vidalar & Civatalar</a>
                    <a href="kategori.php?kat=kilit">Kilit Sistemleri</a>
                    <a href="kategori.php?kat=mentese">Menteşeler</a>
                    <a href="kategori.php?kat=tesisat">Tesisat Malzemeleri</a>
                </div>
            </li>

            <!-- 5. BOYA & YAPI -->
            <li class="dropdown">
                <a href="kategori.php?kat=boya-yapi"><i class="fa-solid fa-paint-roller"></i> Boya & Yapı</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=boya">İç Cephe Boyaları</a>
                    <a href="kategori.php?kat=dis-boya">Dış Cephe Boyaları</a>
                    <a href="kategori.php?kat=firca">Fırça & Rulolar</a>
                    <a href="kategori.php?kat=yapistirici">Yapıştırıcı & Silikon</a>
                </div>
            </li>

            <!-- 6. İŞ GÜVENLİĞİ -->
            <li class="dropdown">
                <a href="kategori.php?kat=is-guvenligi"><i class="fa-solid fa-hard-hat"></i> İş Güvenliği</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=baret">Baretler</a>
                    <a href="kategori.php?kat=eldiven">İş Eldivenleri</a>
                    <a href="kategori.php?kat=ayakkabi">İş Ayakkabıları</a>
                    <a href="kategori.php?kat=maske">Maske</a>
                    <a href="kategori.php?kat=gozluk">Gözlük</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div class="promotion-bar">
    <div class="ticker-wrapper">
        <span class="ticker-text">
            🎁 Babalar Günü'ne Özel Fırsatları Kaçırmayın! <span id="countdown"></span> kaldı! 🛠️
        </span>
    </div>
</div> 

<script>
function updateCountdown() {
    // Babalar Günü Hedefi: 21 Haziran 2026
    const targetDate = new Date("June 21, 2026 00:00:00").getTime();
    const now = new Date().getTime();
    const gap = targetDate - now;

    // Zaman birimleri
    const second = 1000;
    const minute = second * 60;
    const hour = minute * 60;
    const day = hour * 24;

    // Kalan süreyi hesapla
    const d = Math.floor(gap / day);
    const h = Math.floor((gap % day) / hour);
    const m = Math.floor((gap % hour) / minute);
    const s = Math.floor((gap % minute) / second);

    // Ekrana yazdır
    const display = document.getElementById("countdown");
    if(display) {
        display.innerText = `${d} Gün, ${h} Saat, ${m} Dakika, ${s} Saniye`;
    }
}

// Her saniye güncelle ve ilk açılışta çalıştır
setInterval(updateCountdown, 1000);
updateCountdown();
</script>

