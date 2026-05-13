<?php
session_start();
include 'baglan.php'; 

$secilenKat = isset($_GET['kat']) ? $_GET['kat'] : '';

$anaKategoriler = [
    "el-aletleri"         => ["cekic-balyoz", "tornavida", "pense", "olcum"],
    "elektrikli-aletler" => ["matkap", "taslama", "kaynak", "testere"],
    "bahce-tarim"        => ["sulama", "kesme", "cim", "ilaclama"],
    "hirdavat"           => ["vida", "kilit", "mentese", "tesisat"],
    "boya-yapi"          => ["boya", "dis-cephe", "firca", "yapistirici"],
    "is-guvenligi"       => ["baret", "eldiven", "ayakkabi", "maske", "gozluk"]
];

if (array_key_exists($secilenKat, $anaKategoriler)) {
    $sluglar = $anaKategoriler[$secilenKat];
} else {
    $sluglar = [$secilenKat];
}

$soruIsaretleri = str_repeat('?,', count($sluglar) - 1) . '?';
$params = $sluglar; 
$ek_sorgu = "";

// --- DİNAMİK FİLTRELEME MANTIĞI ---

if (isset($_GET['f']) && $_GET['f'] == 'indirim') {
    $ek_sorgu .= " AND p.OldPrice > p.Price";
}

if (isset($_GET['s']) && $_GET['s'] == 'stok') {
    $ek_sorgu .= " AND s.Quantity > 0";
}

if (isset($_GET['p'])) {
    if ($_GET['p'] == '0-1000') {
        $ek_sorgu .= " AND p.Price < 1000";
    } elseif ($_GET['p'] == '1000-1500') {
        $ek_sorgu .= " AND p.Price BETWEEN 1000 AND 1500";
    } elseif ($_GET['p'] == '1500plus') {
        $ek_sorgu .= " AND p.Price > 1500";
    }
}

// --- SQL SORGUSU (Zaten JOIN yapılmış, harika!) ---
$sorguMetni = "
    SELECT p.*, s.Quantity as StokMiktari 
    FROM products p 
    JOIN categories c ON p.CategoryId = c.Id 
    LEFT JOIN stocks s ON p.Id = s.ProductId 
    WHERE c.Slug IN ($soruIsaretleri) AND p.IsActive = 1 $ek_sorgu
    ORDER BY p.Id DESC
";

$sorgu = $db->prepare($sorguMetni);
$sorgu->execute($params);
$filtrelenmisUrunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>NalburDükkan - <?php echo ucfirst(str_replace('-', ' ', $secilenKat)); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Tükendi Rozeti Stili */
        .out-of-stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #d93025;
            color: white;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 3px;
            z-index: 10;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Stokta yoksa resim efekti */
        .img-grayscale {
            filter: grayscale(100%);
            opacity: 0.6;
        }

        /* Pasif buton stili */
        .btn-disabled {
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
            border: 1.5px solid #e2e8f0 !important;
            cursor: not-allowed !important;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        <div class="category-header">
            <h2 class="section-title"><?php echo strtoupper(str_replace('-', ' ', $secilenKat)); ?> REYONU</h2>
            <hr class="title-divider">
        </div>

        <div class="content-wrapper" style="display: flex; gap: 30px; margin-top: 30px;">
            
            <aside class="filter-sidebar" style="flex: 0 0 250px; background: white; padding: 20px; border-radius: 10px; height: fit-content; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <form action="kategori.php" method="GET">
                    <input type="hidden" name="kat" value="<?php echo htmlspecialchars($secilenKat); ?>">
                    
                    <h4 style="border-bottom: 2px solid #ff6600; padding-bottom: 10px; margin-bottom: 20px;">
                        <i class="fa-solid fa-filter"></i> Filtrele
                    </h4>
                    
                    <div class="filter-group" style="margin-bottom: 25px;">
                        <h5 style="margin-bottom: 10px;">Durum</h5>
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="checkbox" name="f" value="indirim" <?php echo (isset($_GET['f']) && $_GET['f'] == 'indirim') ? 'checked' : ''; ?>> İndirimli Ürünler
                        </label>
                        <label style="display: block; cursor: pointer;">
                            <input type="checkbox" name="s" value="stok" <?php echo (isset($_GET['s']) && $_GET['s'] == 'stok') ? 'checked' : ''; ?>> Stokta Olanlar
                        </label>
                    </div>

                    <div class="filter-group" style="margin-bottom: 25px;">
                        <h5 style="margin-bottom: 10px;">Fiyat Aralığı</h5>
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="radio" name="p" value="0-1000" <?php echo (isset($_GET['p']) && $_GET['p'] == '0-1000') ? 'checked' : ''; ?>> 1000 TL Altı
                        </label>
                        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                            <input type="radio" name="p" value="1000-1500" <?php echo (isset($_GET['p']) && $_GET['p'] == '1000-1500') ? 'checked' : ''; ?>> 1000 - 1500 TL
                        </label>
                        <label style="display: block; cursor: pointer;">
                            <input type="radio" name="p" value="1500plus" <?php echo (isset($_GET['p']) && $_GET['p'] == '1500plus') ? 'checked' : ''; ?>> 1500 TL Üstü
                        </label>
                    </div>

                    <button type="submit" class="filter-btn" style="width: 100%; background: #ff6600; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-bottom: 10px;">Sonuçları Uygula</button>
                    <a href="kategori.php?kat=<?php echo $secilenKat; ?>" class="clear-filters" style="display: block; text-align: center; color: #64748b; text-decoration: none; font-size: 13px;">Filtreleri Temizle</a>
                </form>
            </aside>

            <div class="product-grid" style="flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
                <?php if(empty($filtrelenmisUrunler)): ?>
                    <div class="no-products" style="grid-column: 1 / -1; text-align: center; padding: 100px; background: white; border-radius: 10px;">
                        <i class="fa-solid fa-magnifying-glass fa-3x" style="color: #cbd5e1; margin-bottom: 15px;"></i>
                        <p style="color: #64748b;">Bu kriterlere uygun ürün bulunamadı.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($filtrelenmisUrunler as $urun): ?>
                        <div class="product-card" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.05); transition: 0.3s; border: 1px solid #f1f5f9; position: relative;">
                            
                            <?php if (isset($urun['StokMiktari']) && $urun['StokMiktari'] <= 0): ?>
                                <div class="out-of-stock-badge">Tükendi</div>
                            <?php endif; ?>

                            <a href="favori_islem.php?id=<?php echo $urun['Id']; ?>" style="position: absolute; top: 15px; right: 15px; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); color: #cbd5e1; z-index: 1;">
                                <i class="fa-regular fa-heart"></i>
                            </a>

                            <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>" style="text-decoration: none;">
                                <div class="card-image" style="height: 200px; display: flex; align-items: center; justify-content: center; padding: 20px;">
                                    <img src="<?php echo $urun['ImagePath']; ?>" 
                                         alt="<?php echo htmlspecialchars($urun['Name']); ?>" 
                                         style="max-width: 100%; max-height: 100%; object-fit: contain;"
                                         class="<?php echo ($urun['StokMiktari'] <= 0) ? 'img-grayscale' : ''; ?>">
                                </div>
                            </a>

                            <div class="card-details" style="padding: 20px; border-top: 1px solid #f1f5f9;">
                                <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>" style="text-decoration: none; color: inherit;">
                                    <h3 style="font-size: 15px; margin: 0 0 10px 0; height: 40px; overflow: hidden; line-height: 1.4; color: #1e293b;"><?php echo htmlspecialchars($urun['Name']); ?></h3>
                                </a>
                                
                                <div class="price-container" style="margin-bottom: 20px;">
                                    <?php if(!empty($urun['OldPrice']) && $urun['OldPrice'] > $urun['Price']): ?>
                                        <span style="text-decoration: line-through; color: #94a3b8; font-size: 14px; margin-right: 10px;"><?php echo number_format($urun['OldPrice'], 2, ',', '.'); ?> TL</span>
                                    <?php endif; ?>
                                    <span style="color: #ff6600; font-weight: 800; font-size: 20px;"><?php echo number_format($urun['Price'], 2, ',', '.'); ?> TL</span>
                                </div>

                                <form action="sepet.php" method="POST">
                                    <input type="hidden" name="urun_id" value="<?php echo $urun['Id']; ?>">
                                    <input type="hidden" name="islem" value="ekle">
                                    
                                    <?php if ($urun['StokMiktari'] > 0): ?>
                                        <button type="submit" style="width: 100%; background: white; color: #ff6600; border: 1.5px solid #ff6600; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#ff6600'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='#ff6600';">Sepete Ekle</button>
                                    <?php else: ?>
                                        <button type="button" class="btn-disabled" style="width: 100%; padding: 10px; border-radius: 6px; font-weight: bold;" disabled>Stokta Yok</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>