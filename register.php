<?php

require_once 'db.php';

$hata   = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   $fname = mb_convert_case(strtolower(trim($_POST['fname'])), MB_CASE_TITLE, 'UTF-8');
    $lname = mb_convert_case(strtolower(trim($_POST['lname'])), MB_CASE_TITLE, 'UTF-8');
    $email    = strtolower(trim($_POST['email']));
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];

    if (empty($fname) || empty($email) || empty($password)) {
        $hata = 'Ad, email ve şifre zorunludur.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = 'Geçerli bir email adresi girin.';

    } elseif (strlen($password) < 6) {
        $hata = 'Şifre en az 6 karakter olmalıdır.';

    } else {
        $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $hata = 'Bu email zaten kayıtlı.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("
                INSERT INTO Users (RoleId, FName, LName, Email, Phone, Password_hash)
                VALUES (3, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$fname, $lname, $email, $phone, $hash]);

            // Kayıt başarılıysa direkt login'e yönlendir
            header('Location: login.php?kayit=basarili');
            exit;
        }
    }
}

require_once 'header.php';
?>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.auth-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 15px;
}
.auth-card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 40px;
    width: 100%;
    max-width: 460px;
}
.auth-card h2 {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    text-align: center;
}
.auth-card input {
    width: 100%;
    padding: 12px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    outline: none;
    transition: border-color 0.3s;
    box-sizing: border-box;
}
.auth-card input:focus {
    border-color: #e11d61;
}
.auth-btn {
    width: 100%;
    background-color: #333;
    color: white;
    border: none;
    padding: 13px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 5px;
}
.auth-btn:hover {
    background-color: #e11d61;
}
.auth-link {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #666;
}
.auth-link a {
    color: #e11d61;
    font-weight: 600;
}
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
.input-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
</style>

<div class="auth-container">
    <div class="auth-card">
        <h2><i class="fa-solid fa-user-plus"></i> Üye Ol</h2>

        <?php if ($hata): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($hata) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- Ad ve Soyad yan yana -->
            <div class="input-row">
                <input type="text"
                       name="fname"
                       placeholder="Adınız"
                       value="<?= isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : '' ?>"
                       required>

                <input type="text"
                       name="lname"
                       placeholder="Soyadınız"
                       value="<?= isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : '' ?>">
            </div>

            <input type="email"
                   name="email"
                   placeholder="E-posta adresiniz"
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                   required>

            <input type="text"
                   name="phone"
                   placeholder="Telefon numaranız "
                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">

            <input type="password"
                   name="password"
                   placeholder="Şifreniz (en az 6 karakter)"
                   required>

            <button type="submit" class="auth-btn">
                <i class="fa-solid fa-user-plus"></i> Üye Ol
            </button>
        </form>

        <div class="auth-link">
            Zaten hesabın var mı? <a href="login.php">Giriş Yap</a>
        </div>
    </div>
</div>