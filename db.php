<?php 

// db.php — veritabanı bağlantısı 

  

$host     = 'localhost'; 

$dbname   = 'nalbur_db'; 

$username = 'root';       // XAMPP varsayılan kullanıcı 

$password = '';           // XAMPP varsayılan şifre (boş) 

  

try { 

    $pdo = new PDO( 

        "mysql:host=$host;dbname=$dbname;charset=utf8", 

        $username, 

        $password 

    ); 

    // Hataları exception olarak fırlat 

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

     

} catch (PDOException $e) { 

    die("Bağlantı hatası: " . $e->getMessage()); 

} 

?> 