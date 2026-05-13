<?php
// Hataları görebilmek için
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'baglan.php'; // Veritabanına bağlan
include 'urunler.php'; // Eski statik dizilerimizi dahil et

$katSorgu = $db->query("SELECT Id, Slug FROM Categories");
$kategoriler = [];
while($row = $katSorgu->fetch(PDO::FETCH_ASSOC)) {
    $kategoriler[$row['Slug']] = $row['Id'];
}

$tumUrunler = array_merge($indirimliUrunler, $cokSatanlar, $kategoriUrunleri);

$eklenenUrunSayisi = 0;
$atlanilanUrunSayisi = 0;

echo "<h2>Veri Göçü (Data Migration) Başladı...</h2>";

foreach ($tumUrunler as $urun) {
    $katSlug = isset($urun['kat']) ? $urun['kat'] : '';
    $categoryId = isset($kategoriler[$katSlug]) ? $kategoriler[$katSlug] : 1; 

    $baslik = isset($urun['baslik']) ? $urun['baslik'] : 'İsimsiz Ürün';
    $resim = isset($urun['resim']) ? $urun['resim'] : 'fotolar/default.png';
    $aciklama = isset($urun['aciklama']) ? $urun['aciklama'] : '';
    $sku = 'SKU-' . rand(10000, 99999);
    
    // --- AKILLI FİYAT TEMİZLEME ALGORİTMASI ---
    $fiyatHam = isset($urun['fiyat']) ? (string)$urun['fiyat'] : '0';
    $fiyatHam = str_replace(',', '.', $fiyatHam); // Virgülleri noktaya çevir
    $parcalar = explode('.', $fiyatHam);
    
    if (count($parcalar) > 2) {
        // "3.070.00" gibi birden fazla nokta varsa
        $ondalik = array_pop($parcalar);
        $fiyat = implode('', $parcalar) . '.' . $ondalik;
    } elseif (count($parcalar) == 2) {
        // "2.599" (binlik) mi yoksa "134.99" (ondalık) mu kontrolü
        if (strlen($parcalar[1]) == 3) {
            $fiyat = $parcalar[0] . $parcalar[1]; // Noktayı sil (2599 olur)
        } else {
            $fiyat = $fiyatHam; // Dokunma (134.99 kalır)
        }
    } else {
        $fiyat = $fiyatHam;
    }
    // ----------------------------------------

    try {
        $kontrol = $db->prepare("SELECT Id FROM Products WHERE Name = ?");
        $kontrol->execute([$baslik]);
        
        if ($kontrol->rowCount() > 0) {
            $atlanilanUrunSayisi++;
            continue; 
        }

        $ekle = $db->prepare("INSERT INTO Products (CategoryId, Name, Price, Description, ImagePath, Unit, SKU) VALUES (?, ?, ?, ?, ?, 'Adet', ?)");
        $ekle->execute([$categoryId, $baslik, $fiyat, $aciklama, $resim, $sku]);
        
        $yeniUrunId = $db->lastInsertId(); 
        $eklenenUrunSayisi++;

        if (isset($urun['ozellikler']) && is_array($urun['ozellikler'])) {
            $ozellikEkle = $db->prepare("INSERT INTO ProductFeatures (ProductId, FeatureText) VALUES (?, ?)");
            foreach ($urun['ozellikler'] as $ozellik) {
                $ozellikEkle->execute([$yeniUrunId, $ozellik]);
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Hata ($baslik): " . $e->getMessage() . "</p>";
    }
}

echo "<h1 style='color:green;'>İşlem Tamamlandı!</h1>";
echo "<p>Başarıyla Eklenen Yeni Ürün: <b>$eklenenUrunSayisi</b></p>";
echo "<p>Zaten Var Olduğu İçin Atlanan Ürün: <b>$atlanilanUrunSayisi</b></p>";
?>