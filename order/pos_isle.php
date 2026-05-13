<?php
session_start();
include 'baglan.php'; // Veritabanı bağlantısı ($db)

// Sadece yetkili personel
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    echo json_encode(["status" => "error", "message" => "Yetkisiz erişim!"]);
    exit;
}

// Javascript'ten gelen JSON verisini PHP dizisine çevir
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!$data || empty($data['items'])) {
    echo json_encode(["status" => "error", "message" => "Sepet verisi alınamadı."]);
    exit;
}

$staffId = $_SESSION['user_id']; // admin_panel.php'de kullandığın session değişkeni
$totalPrice = $data['totalPrice'];
$items = $data['items'];

try {
    // 1. Transaction Başlat (Eğer biri hata verirse, hiçbiri kaydedilmeyecek)
    $db->beginTransaction();

    // 2. Orders Tablosuna Ekle (Müşteri bilgisi yok, kasa satışı)
    $stmtOrder = $db->prepare("INSERT INTO orders (UserId, TotalPrice, Channel, Status, CreatedByStaffId, PaymentMethod) VALUES (NULL, :total, 'Dükkan', 'completed', :staffId, 'Kasa Nakit/Kredi Kartı')");
    $stmtOrder->execute([
        ':total' => $totalPrice,
        ':staffId' => $staffId
    ]);
    
    $orderId = $db->lastInsertId();

    // Sorguları hazırlayalım (Döngü içinde hız kazanmak için)
    $stmtDetail = $db->prepare("INSERT INTO orderdetails (OrderId, ProductId, Quantity, UnitPrice, SubTotal) VALUES (:oid, :pid, :qty, :price, :subtotal)");
    
    $stmtProdStock = $db->prepare("UPDATE products SET Stock = Stock - :qty WHERE Id = :pid");
    
    $stmtStockTbl = $db->prepare("UPDATE stocks SET Quantity = Quantity - :qty WHERE ProductId = :pid");
    
    $stmtMovement = $db->prepare("INSERT INTO stockmovements (ProductId, RelatedOrderId, PerformedByUserId, MovementType, QuantityDelta, Notes) VALUES (:pid, :oid, :uid, 'OUT', :qty, 'Dükkan POS Satışı')");

    // 3. Her bir ürün için işlemleri yap
    foreach ($items as $item) {
        // Sipariş Detayı
        $stmtDetail->execute([
            ':oid' => $orderId,
            ':pid' => $item['productId'],
            ':qty' => $item['quantity'],
            ':price' => $item['price'],
            ':subtotal' => $item['subtotal']
        ]);

        // Ana Tablodan Stok Düş
        $stmtProdStock->execute([
            ':qty' => $item['quantity'], 
            ':pid' => $item['productId']
        ]);

        // İkincil Stok Tablosundan Düş
        $stmtStockTbl->execute([
            ':qty' => $item['quantity'], 
            ':pid' => $item['productId']
        ]);

        // Stok Hareket Raporuna Log Yaz
        $stmtMovement->execute([
            ':pid' => $item['productId'],
            ':oid' => $orderId,
            ':uid' => $staffId,
            ':qty' => $item['quantity']
        ]);
    }

    // 4. Her şey sorunsuzsa veritabanına onayla
    $db->commit();
    echo json_encode(["status" => "success", "order_id" => $orderId]);

} catch (Exception $e) {
    // 5. Herhangi bir aşamada SQL hatası olursa tüm işlemleri geri al
    $db->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>