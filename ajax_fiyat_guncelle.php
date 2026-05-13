<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece adminler yapabilir
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    echo "error";
    exit;
}

if (isset($_POST['id']) && isset($_POST['fiyat'])) {
    $id = $_POST['id'];
    $fiyat = $_POST['fiyat'];

    try {
        // Products tablosundaki Price sütununu güncelliyoruz
        $sorgu = $db->prepare("UPDATE products SET Price = :fiyat WHERE Id = :id");
        $sonuc = $sorgu->execute([
            ':fiyat' => $fiyat,
            ':id' => $id
        ]);

        if($sonuc) {
            echo "ok";
        } else {
            echo "hata";
        }
    } catch (Exception $e) {
        echo "hata";
    }
}
?>