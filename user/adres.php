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

// --- ADRES SİL ---
if (isset($_GET['sil'])) {
    $adres_id = (int)$_GET['sil'];
    // Sadece kendi adresini silebilsin
    $stmt = $pdo->prepare("DELETE FROM Addresses WHERE Id = ? AND UserId = ?");
    $stmt->execute([$adres_id, $kullanici_id]);
    header("Location: adres.php");
    exit();
}

// --- VARSAYILAN ADRES YAP ---
if (isset($_GET['varsayilan'])) {
    $adres_id = (int)$_GET['varsayilan'];
    // Önce hepsini 0 yap
    $stmt = $pdo->prepare("UPDATE Addresses SET IsDefault = 0 WHERE UserId = ?");
    $stmt->execute([$kullanici_id]);
    // Seçileni 1 yap
    $stmt = $pdo->prepare("UPDATE Addresses SET IsDefault = 1 WHERE Id = ? AND UserId = ?");
    $stmt->execute([$adres_id, $kullanici_id]);
    header("Location: adres.php");
    exit();
}

// --- YENİ ADRES EKLE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name']);
    $street     = trim($_POST['street']);
    $city       = trim($_POST['city']);
    $district   = trim($_POST['district']);
    $postalcode = trim($_POST['postalcode']);

    if (empty($name) || empty($street) || empty($city)) {
        $hata = 'Adres adı, sokak ve şehir zorunludur.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO Addresses (UserId, Name, Street, City, District, PostalCode, IsDefault)
            VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([$kullanici_id, $name, $street, $city, $district, $postalcode]);
        $basari = 'Adres eklendi.';
    }
}

// Mevcut adresleri çek
$stmt = $pdo->prepare("SELECT * FROM Addresses WHERE UserId = ? ORDER BY IsDefault DESC");
$stmt->execute([$kullanici_id]);
$adresler = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.adres-container { max-width: 700px; margin: 40px auto; padding: 0 15px; }
.adres-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 30px;
    margin-bottom: 20px;
}
.adres-card h2 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}
.adres-satir {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 10px;
}
.adres-satir.varsayilan { border-color: #e11d61; }
.adres-adi { font-weight: 600; color: #333; margin-bottom: 4px; }
.adres-detay { font-size: 13px; color: #666; }
.adres-butonlar { display: flex; gap: 10px; }
.btn-varsayilan {
    background: none;
    border: 1px solid #e11d61;
    color: #e11d61;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
}
.btn-sil {
    background: none;
    border: 1px solid #dc2626;
    color: #dc2626;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
}
.varsayilan-badge {
    background: #e11d61;
    color: white;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
    margin-left: 8px;
}
.form-input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 12px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.3s;
}
.form-input:focus { border-color: #e11d61; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.kaydet-btn {
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
.kaydet-btn:hover { background: #e11d61; }
.error-msg {
    background: #fef2f2; border: 1px solid #fecaca;
    color: #dc2626; padding: 10px 15px;
    border-radius: 6px; font-size: 13px; margin-bottom: 15px;
}
.success-msg {
    background: #f0fdf4; border: 1px solid #bbf7d0;
    color: #16a34a; padding: 10px 15px;
    border-radius: 6px; font-size: 13px; margin-bottom: 15px;
}
.back-link { text-align: center; margin-top: 15px; font-size: 14px; }
.back-link a { color: #e11d61; font-weight: 600; }
label { font-size: 13px; font-weight: 600; color: #555;
        margin-bottom: 5px; display: block; }
</style>

<div class="adres-container">

    <!-- Mevcut Adresler -->
    <div class="adres-card">
        <h2><i class="fa-solid fa-location-dot"></i> Adreslerim</h2>

        <?php if (empty($adresler)): ?>
            <p style="color:#666; text-align:center;">Henüz kayıtlı adresiniz yok.</p>
        <?php else: ?>
            <?php foreach ($adresler as $adres): ?>
                <div class="adres-satir <?= $adres['IsDefault'] ? 'varsayilan' : '' ?>">
                    <div>
                        <div class="adres-adi">
                            <?= htmlspecialchars($adres['Name']) ?>
                            <?php if ($adres['IsDefault']): ?>
                                <span class="varsayilan-badge">Varsayılan</span>
                            <?php endif; ?>
                        </div>
                        <div class="adres-detay">
                            <?= htmlspecialchars($adres['Street']) ?>,
                            <?= htmlspecialchars($adres['District']) ?>/
                            <?= htmlspecialchars($adres['City']) ?>
                            <?php if ($adres['PostalCode']): ?>
                                - <?= htmlspecialchars($adres['PostalCode']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="adres-butonlar">
                        <?php if (!$adres['IsDefault']): ?>
                            <a href="adres.php?varsayilan=<?= $adres['Id'] ?>"
                               class="btn-varsayilan">Varsayılan Yap</a>
                        <?php endif; ?>
                        <a href="adres.php?sil=<?= $adres['Id'] ?>"
                           class="btn-sil"
                           onclick="return confirm('Bu adresi silmek istiyor musunuz?')">
                           Sil
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Yeni Adres Ekle -->
    <div class="adres-card">
        <h2><i class="fa-solid fa-plus"></i> Yeni Adres Ekle</h2>

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
            <label>Adres Adı (Ev, İş, Yazlık...)</label>
            <input type="text" name="name" class="form-input"
                   placeholder="Örn: Ev" required>

            <label>Sokak / Cadde</label>
            <input type="text" name="street" class="form-input"
                   placeholder="Örn: Atatürk Cad. No:5" required>

            <div class="form-row">
                <div>
                    <label>Şehir</label>
                    <input type="text" name="city" class="form-input"
                           placeholder="İstanbul" required>
                </div>
                <div>
                    <label>İlçe</label>
                    <input type="text" name="district" class="form-input"
                           placeholder="Kadıköy">
                </div>
            </div>

            <label>Posta Kodu</label>
            <input type="text" name="postalcode" class="form-input"
                   placeholder="34710">

            <button type="submit" class="kaydet-btn">
                <i class="fa-solid fa-plus"></i> Adresi Kaydet
            </button>
        </form>

        <div class="back-link">
            <a href="kullanicibilgileri.php">← Bilgilerime Dön</a>
        </div>
    </div>
</div>