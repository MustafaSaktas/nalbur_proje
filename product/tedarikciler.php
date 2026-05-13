<?php
session_start();
include 'baglan.php';

// Güvenlik Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

$mesaj = "";

// 1. Yeni Tedarikçi Ekleme İşlemi (Tablo şemana tam uyumlu)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tedarikci_ekle'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    try {
        $ekle = $db->prepare("INSERT INTO suppliers (Name, ContactPersonName, Phone, Email) VALUES (?, ?, ?, ?)");
        $ekle->execute([$name, $contact, $phone, $email]);
        $mesaj = "<div style='background:#dcfce3; color:#166534; padding:15px; border-radius:8px; margin-bottom:20px;'>Tedarikçi başarıyla kaydedildi.</div>";
    } catch (Exception $e) {
        $mesaj = "<div style='background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; margin-bottom:20px;'>Hata: " . $e->getMessage() . "</div>";
    }
}

// 2. Tedarikçileri Listeleme
$tedarikciler = $db->query("SELECT * FROM suppliers ORDER BY Id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tedarikçi Yönetimi - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 30px; background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #475569; font-size: 14px; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; outline: none; }
        .input-group input:focus { border-color: #ff6600; }
        
        .btn-save { background: #ff6600; color: white; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-save:hover { background: #e65c00; }

        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .modern-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-container">
        <h2 style="color: #1e293b;"><i class="fa-solid fa-truck-field" style="color: #ff6600;"></i> Tedarikçi Yönetimi</h2>

        <?php echo $mesaj; ?>

        <div class="card">
            <h3 style="margin-top: 0; font-size: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">Yeni Tedarikçi Ekle</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="input-group">
                        <label>Firma Adı (Name):</label>
                        <input type="text" name="name" placeholder="Örn: Bosch Türkiye" required>
                    </div>
                    <div class="input-group">
                        <label>Yetkili (ContactPersonName):</label>
                        <input type="text" name="contact" placeholder="Ad Soyad">
                    </div>
                    <div class="input-group">
                        <label>Telefon (Phone):</label>
                        <input type="text" name="phone" placeholder="05xx xxx xx xx">
                    </div>
                    <div class="input-group">
                        <label>E-Posta (Email):</label>
                        <input type="email" name="email" placeholder="ornek@firma.com">
                    </div>
                </div>
                <button type="submit" name="tedarikci_ekle" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Tedarikçiyi Kaydet
                </button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">Kayıtlı Tedarikçiler</h3>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Firma Adı</th>
                        <th>Yetkili Kişi</th>
                        <th>Telefon</th>
                        <th>E-Posta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($tedarikciler)): ?>
                        <tr><td colspan="4" style="text-align:center; padding: 30px; color: #94a3b8;">Henüz bir tedarikçi eklenmemiş.</td></tr>
                    <?php else: ?>
                        <?php foreach($tedarikciler as $t): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($t['Name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($t['ContactPersonName'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($t['Phone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($t['Email'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>