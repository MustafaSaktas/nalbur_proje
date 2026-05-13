<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece adminler
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

// KRİTİK STOK SORGUSU (Join ve Karakter Fonksiyonu kullanımı)
// Sadece stok miktarı, minimum stok değerine eşit veya küçük olan ürünleri getirir.
$sorgu = $db->query("
    SELECT 
        UPPER(p.Name) as ProductName, 
        s.Quantity, 
        s.MinStock, 
        p.ImagePath,
        (s.MinStock - s.Quantity) as NeededQty
    FROM stocks s
    JOIN products p ON s.ProductId = p.Id
    WHERE s.Quantity <= s.MinStock
    ORDER BY s.Quantity ASC
");
$kritikUrunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kritik Stok Raporu - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 30px; background: #f8fafc; min-height: 100vh; }
        .report-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 13px; border-bottom: 2px solid #e2e8f0; }
        .modern-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .product-img { width: 40px; height: 40px; border-radius: 4px; object-fit: contain; margin-right: 10px; vertical-align: middle; }
        .badge-danger { background: #fee2e2; color: #ef4444; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-container">
        <div style="margin-bottom: 25px;">
            <h2 style="color: #1e293b;"><i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> Kritik Stok Raporu</h2>
            <p style="color: #64748b;">Miktarı belirlenen limitin altına düşen ürünler listelenmektedir.</p>
        </div>

        <div class="report-card">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Mevcut Stok</th>
                        <th>Min. Stok Limiti</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($kritikUrunler)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px;">Tüm stoklar güvenli seviyede.</td></tr>
                    <?php else: ?>
                        <?php foreach($kritikUrunler as $urun): ?>
                        <tr>
                            <td>
                                <img src="<?php echo $urun['ImagePath']; ?>" class="product-img">
                                <strong><?php echo htmlspecialchars($urun['ProductName']); ?></strong>
                            </td>
                            <td style="color: #ef4444; font-weight: bold;"><?php echo $urun['Quantity']; ?> Adet</td>
                            <td><?php echo $urun['MinStock']; ?> Adet</td>
                            <td>
                                <span class="badge-danger">ACİL TEDARİK LAZIM</span>
                            </td>
                            <td>
                                <a href="satinalma_olustur.php" style="color: #ff6600; text-decoration: none; font-weight: bold;">
                                    <i class="fa-solid fa-cart-plus"></i> Sipariş Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>