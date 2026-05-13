<?php
session_start();
include 'baglan.php';

// Güvenlik: Admin değilse veya ID yoksa durdur
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1 || !isset($_SESSION['user_id'])) {
    die('yetkisiz_veya_oturum_yok');
}

if (isset($_POST['id']) && isset($_POST['stok'])) {
    $id = intval($_POST['id']);
    $yeniStok = intval($_POST['stok']);
    $adminId = $_SESSION['user_id']; // Oturumdaki admin ID'sini alıyoruz

    try {
        $db->beginTransaction();

        // 1. Ürün tablosundaki eski stok alanını güncelle (Arayüz uyumu için)
        $db->prepare("UPDATE products SET Stock = ? WHERE Id = ?")->execute([$yeniStok, $id]);

        // 2. Asıl stok tablosunu güncelle (Filtrelerin baktığı yer)
        $stokSql = "INSERT INTO stocks (ProductId, Quantity, MinStock) VALUES (?, ?, 5) 
                    ON DUPLICATE KEY UPDATE Quantity = ?";
        $db->prepare($stokSql)->execute([$id, $yeniStok, $yeniStok]);

        // 3. Stok Hareket Kaydı (Hatanın çıktığı yer burasıydı, burayı düzelttik)
        $hareketSql = "INSERT INTO stockmovements 
                       (ProductId, RelatedOrderId, PerformedByUserId, MovementType, QuantityDelta, Notes) 
                       VALUES (?, NULL, ?, 'ADJUSTMENT', ?, ?)";
        
        $hareketLog = $db->prepare($hareketSql);
        // Değişkenleri sırasıyla veriyoruz: ProductId, PerformedByUserId, QuantityDelta, Notes
        $hareketLog->execute([$id, $adminId, $yeniStok, "Admin paneli üzerinden manuel stok güncelleme yapıldı."]);

        $db->commit();
        echo 'ok';

    } catch (Exception $e) {
        $db->rollBack();
        // Hatanın ne olduğunu tam görebilmek için (Test bittikten sonra burayı 'hata' yapabilirsin)
        echo "Hata: " . $e->getMessage();
    }
}
?>