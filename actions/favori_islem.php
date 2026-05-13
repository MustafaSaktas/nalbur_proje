<?php
session_start();
include 'baglan.php';

// 1. Güvenlik: Giriş yapmayan favori ekleyemez
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php?hata=once_giris_yap");
    exit;
}

if (isset($_GET['id'])) {
    $u_id = $_SESSION['user_id'];
    $p_id = intval($_GET['id']);

    // 2. Kontrol: Zaten favoride mi?
    $kontrol = $db->prepare("SELECT * FROM Favorites WHERE UserId = ? AND ProductId = ?");
    $kontrol->execute([$u_id, $p_id]);

    if ($kontrol->rowCount() > 0) {
        // Varsa favoriden çıkar (Kalbe tekrar basınca silme özelliği)
        $islem = $db->prepare("DELETE FROM Favorites WHERE UserId = ? AND ProductId = ?");
        $islem->execute([$u_id, $p_id]);
    } else {
        // Yoksa favorilere ekle
        $islem = $db->prepare("INSERT INTO Favorites (UserId, ProductId) VALUES (?, ?)");
        $islem->execute([$u_id, $p_id]);
    }
}

// 3. Dönüş: Geldiği sayfaya geri gönder
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;