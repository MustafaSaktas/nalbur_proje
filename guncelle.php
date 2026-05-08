<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$kullanici_id = $_SESSION['user_id'];
$hata   = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fname  = mb_convert_case(strtolower(trim($_POST['Ad'])),     MB_CASE_TITLE, 'UTF-8');
    $lname  = mb_convert_case(strtolower(trim($_POST['Soyad'])),  MB_CASE_TITLE, 'UTF-8');
    $email  = strtolower(trim($_POST['Email']));
    $telefon = trim($_POST['Telefon']);

    // Boş alan kontrolü
    if (empty($fname) || empty($email)) {
        $hata = 'Ad ve email zorunludur.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = 'Geçerli bir email girin.';

    } else {
        // Email başka kullanıcıda var mı?
        $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Id != ?");
        $stmt->execute([$email, $kullanici_id]);

        if ($stmt->rowCount() > 0) {
            $hata = 'Bu email başka bir hesapta kullanılıyor.';
        } else {
            // Güncelle
            $stmt = $pdo->prepare("
                UPDATE Users 
                SET FName = ?, LName = ?, Email = ?, Phone = ?
                WHERE Id = ?
            ");
            $stmt->execute([$fname, $lname, $email, $telefon, $kullanici_id]);

            // Session'daki ismi de güncelle
            $_SESSION['user_name'] = $fname . ' ' . $lname;

            $basari = 'Bilgileriniz güncellendi.';
        }
    }
}

// Güncel bilgileri tekrar çek
$sorgu = $pdo->prepare("SELECT * FROM Users WHERE Id = ?");
$sorgu->execute([$kullanici_id]);
$kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.guncelle-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 15px;
}
.guncelle-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 40px;
    width: 100%;
    max-width: 460px;
}
.guncelle-card h2 {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    text-align: center;
}
.guncelle-card label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
    display: block;
}
.guncelle-card input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.3s;
}
.guncelle-card input:focus { border-color: #e11d61; }
.guncelle-btn {
    width: 100%;
    background: #333;
    color: white;
    border: none;
    padding: 13px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}
.guncelle-btn:hover { background: #e11d61; }
.error-msg {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 15px;
}
.success-msg {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 15px;
}
.back-link {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}
.back-link a { color: #e11d61; font-weight: 600; }
</style>

<div class="guncelle-container">
    <div class="guncelle-card">
        <h2><i class="fa-solid fa-user-pen"></i> Bilgilerimi Güncelle</h2>

        <?php if ($hata): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($hata) ?>
            </div>
        <?php endif; ?>

        <?php if ($basari): ?>
            <div class="success-msg">
                <i class="fa-solid fa-circle-check"></i> <?= $basari ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Ad</label>
            <input type="text" name="Ad"
                   value="<?= htmlspecialchars($kullanici['FName']) ?>" required>

            <label>Soyad</label>
            <input type="text" name="Soyad"
                   value="<?= htmlspecialchars($kullanici['LName']) ?>">

            <label>Email</label>
            <input type="email" name="Email"
                   value="<?= htmlspecialchars($kullanici['Email']) ?>" required>

            <label>Telefon</label>
            <input type="text" name="Telefon"
                   value="<?= htmlspecialchars($kullanici['Phone']) ?>">

            <button type="submit" class="guncelle-btn">
                <i class="fa-solid fa-floppy-disk"></i> Kaydet
            </button>
        </form>

        <div class="back-link">
            <a href="hesabim.php">← Hesabıma Dön</a>
        </div>
    </div>
</div>