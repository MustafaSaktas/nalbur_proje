<?php
session_start();
require_once 'db.php';

$hata = '';
$basari = '';
if (isset($_GET['kayit']) && $_GET['kayit'] === 'basarili') {
    $basari = 'Kayıt başarılı! Giriş yapabilirsiniz.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
        SELECT u.*, r.Name as RoleName
        FROM Users u
        JOIN Roles r ON u.RoleId = r.Id
        WHERE u.Email = ? AND u.IsActive = 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Password_hash'])) {

        $_SESSION['user_id']   = $user['Id'];
        $_SESSION['user_name'] = $user['FName'] . ' ' . $user['LName'];
        $_SESSION['user_role'] = $user['RoleName'];

        if ($user['RoleName'] === 'admin' || $user['RoleName'] === 'staff') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit;

    } else {
        $hata = 'Email veya şifre hatalı.';
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
    max-width: 420px;
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
</style>

<div class="auth-container">
    <div class="auth-card">
        <h2><i class="fa-regular fa-circle-user"></i> Giriş Yap</h2>

        <?php if ($hata): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($hata) ?>
            </div>
        <?php endif; ?>

        <?php if ($basari): ?>
            <div class="success-msg">
                <i class="fa-solid fa-circle-check"></i>
                <?= $basari ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email"
                   name="email"
                   placeholder="E-posta adresiniz"
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                   required>

            <input type="password"
                   name="password"
                   placeholder="Şifreniz"
                   required>

            <button type="submit" class="auth-btn">
                <i class="fa-solid fa-right-to-bracket"></i> Giriş Yap
            </button>
        </form>

        <div class="auth-link">
            Hesabın yok mu? <a href="register.php">Üye Ol</a>
        </div>
    </div>
</div>