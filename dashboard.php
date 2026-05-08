<?php
require_once 'header_admin.php';
require_once '../db.php';
?>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <h1>Gösterge Paneli</h1>
        <div class="breadcrumb">Hoş geldin, <?= htmlspecialchars($kullanici) ?> 👋</div>
    </div>
    <div class="topbar-right">
        <a href="../index.php" class="site-link">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Siteyi Gör
        </a>
    </div>
</div>

<div class="page-content">

<?php
// === VERİ ÇEKME ===

// Toplam ürün
$toplam_urun = $pdo->query("SELECT COUNT(*) FROM Products WHERE IsActive=1")->fetchColumn();

// Kritik stok (Quantity <= MinStock)
$kritik_stok = $pdo->query("
    SELECT COUNT(*) FROM Stocks s
    JOIN Products p ON s.ProductId = p.Id
    WHERE s.Quantity <= s.MinStock AND p.IsActive = 1
")->fetchColumn();

// Bekleyen tedarik siparişleri
$bekleyen_siparis = $pdo->query("
    SELECT COUNT(*) FROM PurchaseOrders WHERE Status = 'pending'
")->fetchColumn();

// Toplam personel
$toplam_personel = $pdo->query("
    SELECT COUNT(*) FROM Users u
    JOIN Roles r ON u.RoleId = r.Id
    WHERE r.Name IN ('admin','staff') AND u.IsActive = 1
")->fetchColumn();

// Son 5 sipariş
$son_siparisler = $pdo->query("
    SELECT o.Id, o.Channel, o.Status, o.TotalPrice, o.OrderAt,
           CONCAT(u.FName,' ',u.LName) AS MusteriAdi
    FROM Orders o
    LEFT JOIN Users u ON o.UserId = u.Id
    ORDER BY o.OrderAt DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Kritik stoklu ürünler
$kritik_urunler = $pdo->query("
    SELECT p.Name, p.SKU, s.Quantity, s.MinStock
    FROM Stocks s
    JOIN Products p ON s.ProductId = p.Id
    WHERE s.Quantity <= s.MinStock AND p.IsActive = 1
    ORDER BY s.Quantity ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ÖZet KARTLAR -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:25px;">

    <div style="background:#fff; border-radius:10px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                border-left:4px solid #ff6600;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:12px; color:#8899aa; font-weight:600; text-transform:uppercase;">
                    Toplam Ürün
                </div>
                <div style="font-size:32px; font-weight:700; color:#1e2a3a; margin-top:5px;">
                    <?= $toplam_urun ?>
                </div>
            </div>
            <div style="background:#fff3e0; width:50px; height:50px; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-box" style="color:#ff6600; font-size:20px;"></i>
            </div>
        </div>
    </div>

    <div style="background:#fff; border-radius:10px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                border-left:4px solid #c62828;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:12px; color:#8899aa; font-weight:600; text-transform:uppercase;">
                    Kritik Stok
                </div>
                <div style="font-size:32px; font-weight:700; color:#c62828; margin-top:5px;">
                    <?= $kritik_stok ?>
                </div>
            </div>
            <div style="background:#fce4ec; width:50px; height:50px; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#c62828; font-size:20px;"></i>
            </div>
        </div>
    </div>

    <div style="background:#fff; border-radius:10px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                border-left:4px solid #f57f17;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:12px; color:#8899aa; font-weight:600; text-transform:uppercase;">
                    Bekleyen Sipariş
                </div>
                <div style="font-size:32px; font-weight:700; color:#f57f17; margin-top:5px;">
                    <?= $bekleyen_siparis ?>
                </div>
            </div>
            <div style="background:#fff8e1; width:50px; height:50px; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-clock" style="color:#f57f17; font-size:20px;"></i>
            </div>
        </div>
    </div>

    <div style="background:#fff; border-radius:10px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                border-left:4px solid #1565c0;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:12px; color:#8899aa; font-weight:600; text-transform:uppercase;">
                    Toplam Personel
                </div>
                <div style="font-size:32px; font-weight:700; color:#1565c0; margin-top:5px;">
                    <?= $toplam_personel ?>
                </div>
            </div>
            <div style="background:#e3f2fd; width:50px; height:50px; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-users" style="color:#1565c0; font-size:20px;"></i>
            </div>
        </div>
    </div>

</div>

<!-- ALT KISIM: Son Siparişler + Kritik Stok -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    <!-- Son Siparişler -->
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-bag-shopping" style="color:#ff6600;"></i>
            Son Siparişler
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Müşteri</th>
                    <th>Kanal</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($son_siparisler as $s): ?>
                <tr>
                    <td>#<?= $s['Id'] ?></td>
                    <td><?= htmlspecialchars($s['MusteriAdi'] ?? 'Misafir') ?></td>
                    <td>
                        <?php if ($s['Channel'] === 'online'): ?>
                            <span class="badge badge-info">Online</span>
                        <?php else: ?>
                            <span class="badge badge-staff">Mağaza</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($s['TotalPrice'], 2) ?> TL</td>
                    <td>
                        <?php
                        $durum_map = [
                            'pending'   => ['Bekliyor',    'badge-warning'],
                            'confirmed' => ['Onaylandı',   'badge-info'],
                            'shipped'   => ['Kargoda',     'badge-staff'],
                            'delivered' => ['Teslim',      'badge-success'],
                            'cancelled' => ['İptal',       'badge-danger'],
                        ];
                        $d = $durum_map[$s['Status']] ?? ['Bilinmiyor', 'badge-customer'];
                        ?>
                        <span class="badge <?= $d[1] ?>"><?= $d[0] ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:15px;">
            <a href="siparisler.php" class="btn btn-primary btn-sm">
                Tümünü Gör <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Kritik Stok -->
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-triangle-exclamation" style="color:#c62828;"></i>
            Kritik Stok Uyarıları
        </div>
        <?php if (empty($kritik_urunler)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                Tüm ürünlerin stok seviyeleri normal.
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>SKU</th>
                        <th>Mevcut</th>
                        <th>Min.</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($kritik_urunler as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['Name']) ?></td>
                        <td><code><?= htmlspecialchars($u['SKU']) ?></code></td>
                        <td style="color:#c62828; font-weight:700;">
                            <?= $u['Quantity'] ?>
                        </td>
                        <td><?= $u['MinStock'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:15px;">
                <a href="tedarik_siparisleri.php" class="btn btn-danger btn-sm">
                    Sipariş Ver <i class="fa-solid fa-truck"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

</div><!-- page-content sonu -->

<?php require_once 'footer_admin.php'; ?>