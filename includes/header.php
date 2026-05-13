<header class="main-header">
    <div class="container header-wrapper">
        <div class="logo">
            <a href="index.php"><i class="fa-solid fa-toolbox" style="color: #ff6600;"></i> <span>NalburDükkan</span></a>
        </div>
        
        <form action="arama.php" method="GET" class="search-bar">
            <input type="text" name="q" placeholder="Ürün, kategori veya marka ara..." required>
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="action-item user-dropdown-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <div class="action-text">
                        <span class="title">Merhaba,</span>
                        <span class="sub"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </div>
                    
                    <div class="user-dropdown-content">
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                            <a href="admin_panel.php" style="font-weight: bold; color: #ff6600;">
                                <i class="fa-solid fa-gauge-high"></i> Admin Paneli
                            </a>
                            <hr style="border: 0; border-top: 1px solid #eee; margin: 5px 0;">
                        <?php endif; ?>
                        
                        <a href="favorilerim.php"><i class="fa-solid fa-heart"></i> Favorilerim</a>
                        <a href="siparisler.php"><i class="fa-solid fa-box"></i> Siparişlerim</a>
                        
                        <!-- YENİ EKLENEN: ADRESLERİM LİNKİ -->
                        <a href="adreslerim.php"><i class="fa-solid fa-map-location-dot"></i> Adreslerim</a>
                        
                        <a href="profilim.php"><i class="fa-solid fa-gear"></i> Profil Ayarları</a>
                        <a href="cikis.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Güvenli Çıkış</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="giris.php" class="action-item">
                    <i class="fa-regular fa-user"></i>
                    <div class="action-text">
                        <span class="title">Giriş Yap</span>
                        <span class="sub">veya Üye Ol</span>
                    </div>
                </a>
            <?php endif; ?>

            <a href="sepet.php" class="action-item cart-action">
                <div class="cart-icon-wrapper">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="cart-badge">
                        <?php echo isset($_SESSION['sepet']) ? array_sum($_SESSION['sepet']) : 0; ?>
                    </span>
                </div>
                <div class="action-text"><span class="title">Sepetim</span></div>
            </a>

            <a href="favorilerim.php" class="action-item">
                <div class="cart-icon-wrapper">
                    <i class="fa-regular fa-heart" style="font-size: 22px;"></i>
                </div>
                <div class="action-text">
                    <span class="title">Favorilerim</span>
                </div>
            </a>

        </div> 
    </div> 
</header>

<nav class="category-nav">
    <div class="container">
        <ul>
            <li class="dropdown">
                <a href="kategori.php?kat=el-aletleri"><i class="fa-solid fa-wrench"></i> El Aletleri</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=cekic-balyoz">Çekiçler & Balyozlar</a>
                    <a href="kategori.php?kat=tornavida">Tornavida Setleri</a>
                    <a href="kategori.php?kat=pense">Penseler & Anahtarlar</a>
                    <a href="kategori.php?kat=olcum">Ölçüm Aletleri</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="kategori.php?kat=elektrikli-aletler"><i class="fa-solid fa-screwdriver-wrench"></i> Elektrikli Aletler</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=matkap">Matkaplar</a>
                    <a href="kategori.php?kat=taslama">Taşlama Makinaları</a>
                    <a href="kategori.php?kat=kaynak">Kaynak Makinaları</a>
                    <a href="kategori.php?kat=testere">Elektrikli Testereler</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="kategori.php?kat=bahce-tarim"><i class="fa-solid fa-leaf"></i> Bahçe & Tarım</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=sulama">Sulama Sistemleri</a>
                    <a href="kategori.php?kat=kesme">Budama Makasları</a>
                    <a href="kategori.php?kat=cim">Çim Biçme Makineleri</a>
                    <a href="kategori.php?kat=ilaclama">İlaçlama Pompaları</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="kategori.php?kat=hirdavat"><i class="fa-solid fa-toolbox"></i> Hırdavat</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=vida">Vidalar & Civatalar</a>
                    <a href="kategori.php?kat=kilit">Kilit Sistemleri</a>
                    <a href="kategori.php?kat=mentese">Menteşeler</a>
                    <a href="kategori.php?kat=tesisat">Tesisat Malzemeleri</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="kategori.php?kat=boya-yapi"><i class="fa-solid fa-paint-roller"></i> Boya & Yapı</a>
                <div class="dropdown-content">
                    <a href="kategori.php?kat=boya">İç Cephe Boyaları</a>
                    <a href="kategori.php?kat=dis-cephe">Dış Cephe Boyaları</a>
                    <a href="kategori.php?kat=firca">Fırça & Rulolar</a>
                    <a href="kategori.php?kat=yapistirici">Yapıştırıcı & Silikon</a>
                </div>
            </li>
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
    const targetDate = new Date("June 21, 2026 00:00:00").getTime();
    const now = new Date().getTime();
    const gap = targetDate - now;

    const second = 1000;
    const minute = second * 60;
    const hour = minute * 60;
    const day = hour * 24;

    const d = Math.floor(gap / day);
    const h = Math.floor((gap % day) / hour);
    const m = Math.floor((gap % hour) / minute);
    const s = Math.floor((gap % minute) / second);

    const display = document.getElementById("countdown");
    if(display) {
        display.innerText = `${d} Gün, ${h} Saat, ${m} Dakika, ${s} Saniye`;
    }
}
setInterval(updateCountdown, 1000);
updateCountdown();
</script>