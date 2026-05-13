<?php
session_start();
include 'baglan.php';

// 1. GÜVENLİK KONTROLÜ
// Sadece admin yetkisi olan kullanıcılar stok kabulü yapabilir[cite: 153].
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

// Gelen ID kontrolü
$poId = isset($_POST['po_id']) ? intval($_POST['po_id']) : 0;

if ($poId > 0) {
    // VERİTABANI İŞLEMİ (TRANSACTION) BAŞLATILIYOR 
    $db->beginTransaction();

    try {
        // 2. Siparişteki ürünleri çekiyoruz
        $urunlerSorgu = $db->prepare("SELECT * FROM purchaseorderproducts WHERE PurchaseOrderId = ?");
        $urunlerSorgu->execute([$poId]);
        $liste = $urunlerSorgu->fetchAll(PDO::FETCH_ASSOC);

        foreach($liste as $item) {
            $adet = $item['OrderedQty'];
            $productId = $item['ProductId'];

            // 3. STOCKS Tablosunu Güncelle (Gerçek Envanter)
            // SQL DML: Update işlemi 
            $stokGuncelle = $db->prepare("UPDATE stocks SET Quantity = Quantity + ? WHERE ProductId = ?");
            $stokGuncelle->execute([$adet, $productId]);

            // 4. PRODUCTS Tablosundaki Stok Bilgisini Senkronize Et (Ön Yüz Gösterimi)
            $urunGuncelle = $db->prepare("UPDATE products SET Stock = Stock + ? WHERE Id = ?");
            $urunGuncelle->execute([$adet, $productId]);

            // 5. STOK HAREKETLERİNE (STOCKMOVEMENTS) KAYIT EKLE
            // Bu kısım "Denetim İzi" (Audit Trail) için kritiktir.
            $log = $db->prepare("
                INSERT INTO stockmovements (ProductId, MovementType, QuantityDelta, Notes) 
                VALUES (?, 'IN', ?, 'Tedarik Kabulü - Sipariş No: #')
            ");
            $log->execute([$productId, $adet]);
        }

        // 6. SATIN ALMA EMRİNİN DURUMUNU GÜNCELLE
        // 'received' (Kabul Edildi) durumuna çekiyoruz.
        $db->prepare("UPDATE purchaseorders SET Status = 'received' WHERE Id = ?")->execute([$poId]);

        // TÜM İŞLEMLER BAŞARILIYSA KAYDET
        $db->commit();
        
        // Başarı mesajıyla listeye yönlendir
        header("Location: satinalma_listele.php?durum=success");
        exit;

    } catch (Exception $e) {
        // HERHANGİ BİR HATADA TÜM İŞLEMLERİ GERİ AL (ROLLBACK)
        $db->rollBack();
        header("Location: satinalma_listele.php?durum=error&mesaj=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: satinalma_listele.php?durum=invalid_id");
    exit;
}