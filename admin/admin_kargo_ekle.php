<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece adminler
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

$mesaj = "";

// FORM GÖNDERİLDİĞİNDE (POST İŞLEMİ)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // İnternet Tabanlı Programlama dersi gereksinimi: PHP doğrulaması 
    $firmaAdi = trim($_POST['firma_adi']);
    $telefon = trim($_POST['telefon']);

    if (!empty($firmaAdi)) {
        try {
            // Veritabanı Sistemleri dersi gereksinimi: SQL DML (INSERT) 
            $kaydet = $db->prepare("INSERT INTO shippers (Name, Phone, IsActive) VALUES (?, ?, 1)");
            $kaydet->execute([$firmaAdi, $telefon]);

            $mesaj = "<div style='padding: 15px; background: #dcfce3; color: #166534; border-radius: 8px; margin-bottom: 20px;'>Firma başarıyla eklendi!</div>";
            // 2 saniye sonra listeye yönlendir
            header("refresh:2; url=admin_kargo_firmalari.php");
        } catch (PDOException $e) {
            $mesaj = "<div style='padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;'>Hata: " . $e->getMessage() . "</div>";
        }
    } else {
        $mesaj = "<div style='padding: 15px; background: #fef3c7; color: #92400e; border-radius: 8px; margin-bottom: 20px;'>Lütfen firma adını boş bırakmayın.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Kargo Firması Ekle - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 40px; background: #f8fafc; min-height: 100vh; }
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #475569; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #ff6600; box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1); }
        .btn-submit { background: #ff6600; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-submit:hover { background: #e65c00; }
        .btn-cancel { color: #64748b; text-decoration: none; font-size: 14px; margin-left: 15px; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-container">
        <div style="margin-bottom: 30px;">
            <h2 style="color: #1e293b;"><i class="fa-solid fa-truck-medical" style="color: #ff6600;"></i> Yeni Kargo Firması Ekle</h2>
            <p style="color: #64748b;">Sisteme yeni bir lojistik ortağı tanımlayın.</p>
        </div>

        <?php echo $mesaj; ?>

        <div class="form-card">
            <form action="" method="POST">
                <div class="form-group">
                    <label>Kargo Firması Adı</label>
                    <input type="text" name="firma_adi" placeholder="Örn: Aras Kargo" required>
                </div>
                
                <div class="form-group">
                    <label>İletişim Numarası</label>
                    <input type="text" name="telefon" placeholder="Örn: 444 00 00">
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-submit">Firmayı Kaydet</button>
                    <a href="admin_kargo_firmalari.php" class="btn-cancel">İptal Et</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>