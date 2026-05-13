<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

$mesaj = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; // YENİ: Formdan gelen stok verisini alıyoruz
    $cat_id = $_POST['category_id'];
    $image = $_POST['image_path'];
    $desc = $_POST['description'];
    $sku = "SKU-" . rand(1000, 9999);

    $features = explode("\n", str_replace("\r", "", $_POST['features']));

    try {
        // YENİ: Sorguya 'Stock' sütununu ve değerini ekledik
        $sorgu = $db->prepare("INSERT INTO products (CategoryId, Name, Price, Stock, Description, ImagePath, Unit, SKU) VALUES (?, ?, ?, ?, ?, ?, 'Adet', ?)");
        $sorgu->execute([$cat_id, $name, $price, $stock, $desc, $image, $sku]);
        
        $yeniId = $db->lastInsertId();

        $ozellikSorgu = $db->prepare("INSERT INTO ProductFeatures (ProductId, FeatureText) VALUES (?, ?)");
        foreach ($features as $f) {
            $f = trim($f);
            if (!empty($f)) {
                $ozellikSorgu->execute([$yeniId, $f]);
            }
        }

        $mesaj = "<div style='background: #dcfce3; color: #166534; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Ürün başarıyla eklendi!</div>";
    } catch (PDOException $e) {
        $mesaj = "<div style='background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Hata oluştu: " . $e->getMessage() . "</div>";
    }
}

$kategoriler = $db->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Ürün Ekle | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        .admin-content { margin-left: 260px; padding: 30px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 800px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #334155; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box; font-family: inherit; }
        .btn-submit { background: #ff6600; color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-submit:hover { background: #e65c00; }
        
        /* Fiyat ve Stok alanlarını yan yana daha şık göstermek için küçük bir grid dokunuşu */
        .grid-2-col { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-content">
        <h2 style="margin-top: 0; color: #1e293b;"><i class="fa-solid fa-box"></i> Yeni Ürün Ekle</h2>
        
        <div class="form-container">
            <?php echo $mesaj; ?>
            
            <form action="urun_ekle.php" method="POST">
                <div class="form-group">
                    <label>Ürün Adı</label>
                    <input type="text" name="name" required placeholder="Örn: Bosch Darbeli Matkap">
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category_id">
                        <?php foreach($kategoriler as $k): ?>
                            <option value="<?php echo $k['Id']; ?>"><?php echo htmlspecialchars($k['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Fiyat ve Stok alanlarını yan yana alıyoruz -->
                <div class="grid-2-col">
                    <div class="form-group">
                        <label>Fiyat (TL)</label>
                        <input type="number" step="0.01" name="price" required placeholder="1250.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Stok Adedi</label>
                        <input type="number" name="stock" required placeholder="Örn: 50">
                    </div>
                </div>

                <div class="form-group">
                    <label>Görsel Yolu</label>
                    <input type="text" name="image_path" required placeholder="fotolar/urun.png">
                </div>
                
                <div class="form-group">
                    <label>Ürün Açıklaması</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Teknik Özellikler (Her satıra bir özellik)</label>
                    <textarea name="features" rows="5" placeholder="Dövme Çelik&#10;Ahşap Sap&#10;300gr"></textarea>
                </div>
                
                <button type="submit" class="btn-submit"><i class="fa-solid fa-check"></i> Ürünü Kaydet</button>
                <a href="admin_urunler.php" style="margin-left: 15px; color: #64748b; text-decoration: none;">İptal ve Geri Dön</a>
            </form>
        </div>
    </main>
</body>
</html>