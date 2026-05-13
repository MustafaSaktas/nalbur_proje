<?php
session_start();
include 'baglan.php';

// Güvenlik
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    die("Yetkisiz erişim!");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $db->beginTransaction(); // İşlemi başlat (Atomiklik - Mühendislik kuralı)

        // 1. Önce bu ürüne ait özellikleri sil (Çocuk kayıtlar)
        $s1 = $db->prepare("DELETE FROM ProductFeatures WHERE ProductId = ?");
        $s1->execute([$id]);

        // 2. Sonra ana ürünü sil (Ebeveyn kayıt)
        $s2 = $db->prepare("DELETE FROM Products WHERE Id = ?");
        $s2->execute([$id]);

        $db->commit(); // Her şey tamamsa onayla
        header("Location: admin_panel.php?durum=silindi");
        exit;

    } catch (Exception $e) {
        $db->rollBack(); // Hata çıkarsa işlemi geri al (Veri kaybını önle)
        die("Silme Hatası: " . $e->getMessage());
    }
}