<aside class="admin-sidebar" style="display: flex; flex-direction: column; height: 100vh; position: fixed; width: 260px; background: #1e293b; color: white;">
    
    <div class="sidebar-header" style="padding: 20px; border-bottom: 1px solid #334155; flex-shrink: 0;">
        <i class="fa-solid fa-toolbox logo-icon"></i>
        <div>
            <h1>NalburDükkan</h1>
            <span style="font-size: 12px; color: #94a3b8;">Yönetim Paneli</span>
        </div>
    </div>

    <div class="sidebar-user" style="padding: 15px 20px; flex-shrink: 0;">
        <div class="user-avatar">
            <?php 
                echo isset($_SESSION['user_name']) ? mb_substr($_SESSION['user_name'], 0, 1, 'UTF-8') : 'F'; 
            ?>
        </div>
        <div class="user-info">
            <p><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Fuat Can'); ?></p>
            <span class="badge-admin">Admin Yetkisi</span>
        </div>
    </div>

    <nav class="sidebar-nav" style="flex: 1; overflow-y: auto; padding: 10px 0;">
        <div class="nav-section">GENEL</div>
        <a href="admin_panel.php" class="nav-item">
            <i class="fa-solid fa-gauge-high"></i> Gösterge Paneli
        </a>

        <div class="nav-section">ÜRÜN VE STOK</div>
        <a href="admin_kategoriler.php" class="nav-item">
            <i class="fa-solid fa-tags"></i> Kategoriler
        </a>
        <a href="admin_urunler.php" class="nav-item">
            <i class="fa-solid fa-box-open"></i> Ürün Listesi
        </a>
        <a href="kritik_stok_raporu.php" class="nav-item">
            <i class="fa-solid fa-triangle-exclamation"></i> Kritik Stoklar
        </a>

        <div class="nav-section">SATIŞ</div>
        <a href="pos.php" class="nav-item" style="color: #10b981; font-weight: bold;">
            <i class="fa-solid fa-cash-register"></i> Hızlı Satış (POS)
        </a>
        <a href="admin_siparisler.php" class="nav-item">
            <i class="fa-solid fa-cart-shopping"></i> Müşteri Siparişleri
        </a>

        <div class="nav-section">TEDARİK YÖNETİMİ</div>
        <a href="satinalma_listele.php" class="nav-item">
            <i class="fa-solid fa-file-invoice-dollar"></i> Satın Alma Emirleri
        </a>
        <a href="tedarikciler.php" class="nav-item">
            <i class="fa-solid fa-truck-field"></i> Tedarikçi Listesi
        </a>

        <div class="nav-section">KARGO VE LOJİSTİK</div>
        <a href="admin_kargo_firmalari.php" class="nav-item">
            <i class="fa-solid fa-truck-fast"></i> Kargo Firmaları
        </a>
        <a href="admin_araclar.php" class="nav-item">
            <i class="fa-solid fa-bus"></i> Araç Yönetimi
        </a>
        <a href="admin_suruculer.php" class="nav-item">
            <i class="fa-solid fa-id-card"></i> Sürücü Kayıtları
        </a>

        <div class="nav-section">YÖNETİM</div>
        <a href="admin_kullanicilar.php" class="nav-item">
            <i class="fa-solid fa-users-gear"></i> Kullanıcı Yönetimi
            <span class="badge-sub">Admin</span>
        </a>
    </nav>

    <div class="sidebar-footer" style="padding: 20px; background: #0f172a; border-top: 1px solid #334155; flex-shrink: 0;">
        <a href="index.php" target="_blank" style="display: flex; align-items: center; gap: 10px; color: #94a3b8; text-decoration: none; margin-bottom: 10px;">
            <i class="fa-solid fa-house"></i> Siteye Git
        </a>
        <a href="cikis.php" class="logout" style="display: flex; align-items: center; gap: 10px; color: #ef4444; text-decoration: none; font-weight: bold;">
            <i class="fa-solid fa-right-from-bracket"></i> Güvenli Çıkış
        </a>
    </div>
</aside>