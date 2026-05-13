<?php
// baglan.php
$host = '127.0.0.1'; // 'localhost:8889' yerine bunu dene
$dbname = 'nalbur_db';
$kullanici = 'root';
$sifre = ''; 

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $kullanici, $sifre);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>