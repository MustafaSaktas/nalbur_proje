<?php
// Güvenlik kontrolü — her admin sayfasına dahil edilecek
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giriş yapılmamışsa login'e gönder
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Sadece admin ve staff girebilir
if (!in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header('Location: ../index.php');
    exit();
}

$rol        = $_SESSION['user_role'];
$kullanici  = $_SESSION['user_name'];

// Hangi sayfa aktif — sidebar'da vurgulamak için
$aktif_sayfa = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli — NalburDükkan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: #1e2a3a;
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: width 0.3s;
        }

        .sidebar-logo {
            padding: 25px 20px;
            border-bottom: 1px solid #2d3f55;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo i {
            font-size: 24px;
            color: #ff6600;
        }

        .sidebar-logo span {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .sidebar-logo .panel-text {
            font-size: 11px;
            color: #8899aa;
            display: block;
        }

        /* Kullanıcı bilgisi */
        .sidebar-user {
            padding: 15px 20px;
            border-bottom: 1px solid #2d3f55;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user .avatar {
            width: 38px;
            height: 38px;
            background: #ff6600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .sidebar-user .user-info .name {
            font-size: 13px;
            font-weight: 600;
        }

        .sidebar-user .user-info .role-badge {
            font-size: 11px;
            background: <?= $rol === 'admin' ? '#ff6600' : '#2d7dd2' ?>;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 2px;
        }

        /* Menü */
        .sidebar-menu {
            padding: 15px 0;
            flex: 1;
        }

        .menu-section {
            padding: 8px 20px 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #5a7a99;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: #a0b4c8;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: #2d3f55;
            color: #fff;
            border-left-color: #ff6600;
        }

        .menu-item.aktif {
            background: #2d3f55;
            color: #fff;
            border-left-color: #ff6600;
        }

        .menu-item i {
            width: 18px;
            text-align: center;
            font-size: 15px;
        }

        /* Admin-only menü öğesi */
        .menu-item.admin-only {
            position: relative;
        }

        .menu-item .admin-tag {
            margin-left: auto;
            font-size: 10px;
            background: #ff6600;
            padding: 1px 6px;
            border-radius: 8px;
            color: white;
        }

        /* Alt kısım */
        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid #2d3f55;
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #a0b4c8;
            text-decoration: none;
            font-size: 13px;
            padding: 8px 0;
            transition: color 0.2s;
        }

        .sidebar-footer a:hover { color: #fff; }

        /* ===== ANA İÇERİK ===== */
        .main-content {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Üst bar */
        .topbar {
            background: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left h1 {
            font-size: 20px;
            font-weight: 600;
            color: #1e2a3a;
        }

        .topbar-left .breadcrumb {
            font-size: 12px;
            color: #8899aa;
            margin-top: 2px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-right .site-link {
            font-size: 13px;
            color: #ff6600;
            text-decoration: none;
            border: 1px solid #ff6600;
            padding: 6px 14px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .topbar-right .site-link:hover {
            background: #ff6600;
            color: white;
        }

        /* İçerik alanı */
        .page-content {
            padding: 30px;
            flex: 1;
        }

        /* ===== GENEL KARTLAR ===== */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 25px;
            margin-bottom: 25px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e2a3a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ===== TABLO ===== */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #5a7a99;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eee;
        }

        .admin-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #333;
        }

        .admin-table tr:hover td { background: #fafbfc; }

        /* ===== BADGE ===== */
        .badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-admin    { background: #fff3e0; color: #ff6600; }
        .badge-staff    { background: #e3f2fd; color: #1565c0; }
        .badge-customer { background: #f3f4f6; color: #555; }
        .badge-success  { background: #e8f5e9; color: #2e7d32; }
        .badge-warning  { background: #fff8e1; color: #f57f17; }
        .badge-danger   { background: #fce4ec; color: #c62828; }
        .badge-info     { background: #e3f2fd; color: #1565c0; }

        /* ===== BUTONLAR ===== */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-primary   { background: #1e2a3a; color: #fff; }
        .btn-primary:hover { background: #ff6600; }
        .btn-success   { background: #2e7d32; color: #fff; }
        .btn-success:hover { background: #1b5e20; }
        .btn-danger    { background: #c62828; color: #fff; }
        .btn-danger:hover  { background: #b71c1c; }
        .btn-warning   { background: #f57f17; color: #fff; }
        .btn-warning:hover { background: #e65100; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* ===== FORM ===== */
        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus { border-color: #ff6600; }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        /* ===== ALERT ===== */
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-danger  { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }
        .alert-warning { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { width: 60px; }
            .sidebar-logo span, .menu-item span,
            .sidebar-user .user-info,
            .menu-section { display: none; }
            .main-content { margin-left: 60px; }
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">

    <div class="sidebar-logo">
        <i class="fa-solid fa-toolbox"></i>
        <div>
            <span>NalburDükkan</span>
            <span class="panel-text">Yönetim Paneli</span>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="avatar"><?= mb_substr($kullanici, 0, 1, 'UTF-8') ?></div>
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($kullanici) ?></div>
            <span class="role-badge"><?= $rol === 'admin' ? 'Yönetici' : 'Personel' ?></span>
        </div>
    </div>

    <nav class="sidebar-menu">

        <div class="menu-section">Genel</div>

        <a href="dashboard.php"
           class="menu-item <?= $aktif_sayfa === 'dashboard.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-gauge"></i>
            <span>Gösterge Paneli</span>
        </a>

        <div class="menu-section">Ürün ve Stok</div>

        <a href="kategoriler.php"
           class="menu-item <?= $aktif_sayfa === 'kategoriler.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            <span>Kategoriler</span>
        </a>

        <a href="urunler.php"
           class="menu-item <?= $aktif_sayfa === 'urunler.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-box"></i>
            <span>Ürünler</span>
        </a>

        <div class="menu-section">Tedarik</div>

        <a href="tedarikciler.php"
           class="menu-item <?= $aktif_sayfa === 'tedarikciler.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-truck"></i>
            <span>Tedarikçiler</span>
        </a>

        <a href="tedarik_siparisleri.php"
           class="menu-item <?= $aktif_sayfa === 'tedarik_siparisleri.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>Tedarik Siparişleri</span>
        </a>

        <div class="menu-section">Satış</div>

        <a href="siparisler.php"
           class="menu-item <?= $aktif_sayfa === 'siparisler.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Siparişler</span>
        </a>

        <?php if ($rol === 'admin'): ?>
        <div class="menu-section">Yönetim</div>

        <a href="kullanicilar.php"
           class="menu-item admin-only <?= $aktif_sayfa === 'kullanicilar.php' ? 'aktif' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Kullanıcılar</span>
            <span class="admin-tag">Admin</span>
        </a>
        <?php endif; ?>

    </nav>

    <div class="sidebar-footer">
        <a href="../index.php">
            <i class="fa-solid fa-store"></i>
            <span>Siteye Git</span>
        </a>
        <a href="../logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Çıkış Yap</span>
        </a>
    </div>

</aside>

<!-- Ana içerik başlıyor -->
<div class="main-content">