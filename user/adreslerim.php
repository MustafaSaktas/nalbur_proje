<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

$userID = $_SESSION['user_id'];
$mesaj = "";

// --- VARSAYILAN YAPMA İŞLEMİ ---
if (isset($_GET['varsayilan'])) {
    $adresID = intval($_GET['varsayilan']);
    
    try {
        $db->beginTransaction();
        // 1. Önce kullanıcının tüm adreslerini varsayılan olmaktan çıkar
        $db->prepare("UPDATE addresses SET IsDefault = 0 WHERE UserId = ?")->execute([$userID]);
        // 2. Seçilen adresi varsayılan yap (Sadece silinmemiş adresler için)
        $db->prepare("UPDATE addresses SET IsDefault = 1 WHERE Id = ? AND UserId = ? AND is_deleted = 0")->execute([$adresID, $userID]);
        $db->commit();
        $mesaj = "<div style='background:#dcfce3; color:#166534; padding:10px; border-radius:5px; margin-bottom:15px;'>Varsayılan adres güncellendi.</div>";
    } catch (Exception $e) {
        $db->rollBack();
        $mesaj = "<div style='background:#fee2e2; color:#991b1b; padding:10px; border-radius:5px; margin-bottom:15px;'>Bir hata oluştu.</div>";
    }
}

// --- ADRES SİLME İŞLEMİ (SOFT DELETE) ---
if (isset($_GET['sil'])) {
    $adresID = intval($_GET['sil']);
    
    try {
        // Fiziksel olarak silmek yerine is_deleted sütununu 1 yapıyoruz
        // Bu sayede 500 hatası almazsın ve sipariş geçmişin bozulmaz.
        $sil = $db->prepare("UPDATE addresses SET is_deleted = 1, IsDefault = 0 WHERE Id = ? AND UserId = ?");
        if ($sil->execute([$adresID, $userID])) {
            $mesaj = "<div style='background:#dcfce3; color:#166534; padding:10px; border-radius:5px; margin-bottom:15px;'>Adres başarıyla kaldırıldı.</div>";
        }
    } catch (Exception $e) {
        $mesaj = "<div style='background:#fee2e2; color:#991b1b; padding:10px; border-radius:5px; margin-bottom:15px;'>Adres silinirken bir hata oluştu: İlişkili veriler mevcut.</div>";
    }
}

// Kullanıcının silinmemiş adreslerini çek
$sorgu = $db->prepare("SELECT * FROM addresses WHERE UserId = ? AND is_deleted = 0 ORDER BY IsDefault DESC, Id DESC");
$sorgu->execute([$userID]);
$adresler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Adreslerim - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .address-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .address-card { background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; position: relative; transition: 0.3s; }
        .address-card.is-default { border-color: #ff6600; background: #fffaf5; box-shadow: 0 4px 12px rgba(255,102,0,0.1); }
        .address-title { font-weight: bold; color: #1e293b; font-size: 16px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; }
        .address-text { color: #64748b; font-size: 14px; line-height: 1.5; margin-bottom: 20px; min-height: 45px;}
        .badge-default { background: #ff6600; color: white; padding: 3px 10px; border-radius: 5px; font-size: 11px; font-weight: bold; }
        .card-actions { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .btn-set-default { color: #ff6600; text-decoration: none; font-size: 13px; font-weight: 600; border: 1px solid #ff6600; padding: 5px 10px; border-radius: 5px; transition: 0.3s; }
        .btn-set-default:hover { background: #ff6600; color: white; }
        .btn-delete-addr { color: #94a3b8; font-size: 13px; text-decoration: none; transition: 0.3s; }
        .btn-delete-addr:hover { color: #ef4444; }
        .btn-add-addr { background: #1e293b; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; transition: 0.3s;}
        .btn-add-addr:hover { background: #ff6600; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container" style="margin: 40px auto; max-width: 1000px; padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="margin:0;"><i class="fa-solid fa-map-location-dot" style="color:#ff6600;"></i> Kayıtlı Adreslerim</h2>
            <a href="adres_ekle.php" class="btn-add-addr"><i class="fa-solid fa-plus"></i> Yeni Adres Ekle</a>
        </div>

        <?php echo $mesaj; ?>

        <div class="address-grid">
            <?php if(count($adresler) > 0): ?>
                <?php foreach($adresler as $a): ?>
                    <div class="address-card <?php echo $a['IsDefault'] ? 'is-default' : ''; ?>">
                        <div class="address-title">
                            <span><i class="fa-solid fa-house-user"></i> <?php echo htmlspecialchars($a['Name']); ?></span>
                            <?php if($a['IsDefault']): ?> 
                                <span class="badge-default">Varsayılan</span> 
                            <?php endif; ?>
                        </div>
                        <div class="address-text">
                            <?php echo htmlspecialchars($a['Street']); ?><br>
                            <strong><?php echo htmlspecialchars(($a['District'] ?? 'Bölge') . " / " . ($a['City'] ?? 'Şehir')); ?></strong>
                        </div>
                        
                        <div class="card-actions">
                            <?php if(!$a['IsDefault']): ?>
                                <a href="adreslerim.php?varsayilan=<?php echo $a['Id']; ?>" class="btn-set-default">Varsayılan Yap</a>
                            <?php else: ?>
                                <span style="font-size: 13px; color: #22c55e; font-weight: 600;"><i class="fa-solid fa-check"></i> Seçili</span>
                            <?php endif; ?>
                            
                            <a href="adreslerim.php?sil=<?php echo $a['Id']; ?>" class="btn-delete-addr" onclick="return confirm('Bu adresi silmek istediğinize emin misiniz?')">
                                <i class="fa-solid fa-trash-can"></i> Sil
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; background: white; border-radius: 10px; border: 1px dashed #cbd5e1; width: 100%; grid-column: 1 / -1;">
                    <i class="fa-solid fa-map-marked-alt fa-3x" style="color: #cbd5e1; margin-bottom: 15px;"></i>
                    <p style="color: #64748b; margin: 0;">Henüz kayıtlı bir adresiniz bulunmuyor.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>