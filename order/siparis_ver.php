<?php
session_start();
include 'baglan.php';

// Güvenlik: Kullanıcı giriş yapmamışsa veya sepet boşsa işlemi durdur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && !empty($_SESSION['sepet'])) {
    
    $userId = $_SESSION['user_id'];
    $aliciAd = trim($_POST['alici_ad']);
    $telefon = trim($_POST['telefon']);
    $adresMetni = trim($_POST['adres']);
    $odeme = $_POST['odeme_yontemi'];
    $totalPrice = 0;

    try {
        // Transaction Başlat: Hata olursa hiçbir tabloya yarım yamalak veri girilmez
        $db->beginTransaction();
        
        // 1. Toplam Fiyat Hesapla
        foreach($_SESSION['sepet'] as $id => $adet) {
            $st = $db->prepare("SELECT Price FROM products WHERE Id = ?");
            $st->execute([$id]);
            $u = $st->fetch();
            $totalPrice += ($u['Price'] * $adet);
        }

        // 2. Adres Kaydı
        $adresEkle = $db->prepare("INSERT INTO addresses (UserId, Name, Street, City, IsDefault) VALUES (?, 'Sipariş Adresi', ?, 'Belirtilmedi', 1)");
        $adresEkle->execute([$userId, $adresMetni]);
        $shippingAddressId = $db->lastInsertId();

        // 3. Sipariş Oluştur (Soru işareti sayısı ve veriler 8 adet olarak eşitlendi)
        $stmt = $db->prepare("INSERT INTO orders (UserId, TotalPrice, Channel, Status, ShippingAddressId, ReceiverName, Phone, Address, PaymentMethod) 
                              VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $userId,             // 1
            $totalPrice,          // 2
            'Web Site',           // 3 (Channel)
            $shippingAddressId,   // 4
            $aliciAd,             // 5
            $telefon,             // 6
            $adresMetni,          // 7
            $odeme                // 8
        ]);
        
        $orderId = $db->lastInsertId();

        // 4. Ödeme Kaydı
        $odemeEkle = $db->prepare("INSERT INTO payments (OrderId, PaymentMethod, Amount, Status) VALUES (?, ?, ?, 'Completed')");
        $odemeEkle->execute([$orderId, $odeme, $totalPrice]);

        // 5. Ürün Bazlı İşlemler: Detay, Çift Tablo Stok Düşme ve Loglama
        foreach($_SESSION['sepet'] as $id => $adet) {
            $st = $db->prepare("SELECT Price, Stock FROM products WHERE Id = ?");
            $st->execute([$id]);
            $u = $st->fetch();
            $satirToplami = $u['Price'] * $adet;

            // a. Sipariş Detay kaydı
            $detay = $db->prepare("INSERT INTO orderdetails (OrderId, ProductId, Quantity, UnitPrice, SubTotal) VALUES (?, ?, ?, ?, ?)");
            $detay->execute([$orderId, $id, $adet, $u['Price'], $satirToplami]);

            // b. Ürün tablosundan stok düş (Admin paneli için)
            $stokGuncelle = $db->prepare("UPDATE products SET Stock = Stock - ? WHERE Id = ?");
            $stokGuncelle->execute([$adet, $id]);

            // c. Stok tablosundan stok düş (Filtreleme ve gerçek envanter yönetimi için)
            // Bu satır 12-13 farkını çözen kritik satırdır.
            $stokTabloGuncelle = $db->prepare("UPDATE stocks SET Quantity = Quantity - ? WHERE ProductId = ?");
            $stokTabloGuncelle->execute([$adet, $id]);

            // d. Stok Hareket Kaydı
            $stokLog = $db->prepare("INSERT INTO stockmovements (ProductId, RelatedOrderId, PerformedByUserId, MovementType, QuantityDelta, Notes) VALUES (?, ?, ?, 'OUT', ?, ?)");
            $stokLog->execute([$id, $orderId, $userId, $adet, "Web Siparişi Çıkışı"]);
        }

        // Tüm işlemler başarılıysa veritabanına onayı gönder
        $db->commit();
        
        // Sepeti temizle ve kullanıcıyı yönlendir
        unset($_SESSION['sepet']);
        header("Location: siparisler.php"); 
        exit;

    } catch (Exception $e) {
        // Herhangi bir hata anında her şeyi geri al (Veritabanı bozulmaz)
        $db->rollBack();
        echo "Sipariş oluşturulurken hata meydana geldi: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>