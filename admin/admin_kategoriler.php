<?php
session_start();
include 'baglan.php';

// Güvenlik Kontrolü
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

$mesaj = "";

// 1. KATEGORİ EKLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kategori_adi'])) {
    $ad = trim($_POST['kategori_adi']);
    
    // Türkçe karakterleri çevirip aralara tire koyarak otomatik SEO URL (Slug) oluşturuyoruz
    $slug = mb_strtolower($ad, 'UTF-8');
    $slug = str_replace(
        ['ı', 'ğ', 'ü', 'ş', 'i', 'ö', 'ç', ' '],
        ['i', 'g', 'u', 's', 'i', 'o', 'c', '-'],
        $slug
    );
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug); // Sadece harf, rakam ve tireye izin ver

    try {
        $ekle = $db->prepare("INSERT INTO Categories (Name, Slug) VALUES (?, ?)");
        if($ekle->execute([$ad, $slug])){
            $mesaj = "<div class='alert-success'>Kategori başarıyla eklendi!</div>";
        }
    } catch (PDOException $e) {
        $mesaj = "<div class='alert-danger'>Ekleme hatası: " . $e->getMessage() . "</div>";
    }
}

// 2. KATEGORİ SİLME İŞLEMİ
if (isset($_GET['sil_id'])) {
    $sil_id = $_GET['sil_id'];
    try {
        // Not: Eğer bu kategoriye bağlı ürünler varsa veritabanı (Foreign Key) silmeye izin vermeyebilir.
        $db->prepare("DELETE FROM Categories WHERE Id = ?")->execute([$sil_id]);
        header("Location: admin_kategoriler.php?durum=silindi");
        exit;
    } catch (PDOException $e) {
        $mesaj = "<div class='alert-danger'>Hata: Bu kategoriye ait ürünler olduğu için silemezsiniz. Önce ürünleri silin.</div>";
    }
}

// 3. KATEGORİLERİ ÇEK
$kategoriler = $db->query("SELECT * FROM Categories ORDER BY Id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategoriler - Admin Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif;}
        .admin-content { margin-left: 260px; padding: 30px; }
        
        .grid-container { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; color: #1e293b; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #475569; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; box-sizing: border-box; }
        .btn-add { background: #ff6600; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; }
        
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #f8fafc; padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0; color: #475569; }
        .admin-table td { padding: 15px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: bold; }
        .btn-delete:hover { text-decoration: underline; }

        .alert-success { background: #dcfce3; color: #166534; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alert-danger { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-content">
        <h2 style="margin-top: 0; color: #1e293b;"><i class="fa-solid fa-tags"></i> Kategori Yönetimi</h2>
        
        <?php 
            echo $mesaj; 
            if(isset($_GET['durum']) && $_GET['durum'] == 'silindi'){
                echo "<div class='alert-success'>Kategori başarıyla silindi!</div>";
            }
        ?>

        <div class="grid-container">
            <!-- Sol Panel: Kategori Ekleme Formu -->
            <div class="card">
                <h3>Yeni Kategori Ekle</h3>
                <form method="POST" action="admin_kategoriler.php">
                    <div class="form-group">
                        <label>Kategori Adı</label>
                        <input type="text" name="kategori_adi" required placeholder="Örn: El Aletleri">
                    </div>
                    <button type="submit" class="btn-add"><i class="fa-solid fa-plus"></i> Kaydet</button>
                </form>
            </div>

            <!-- Sağ Panel: Mevcut Kategoriler -->
            <div class="card">
                <h3>Kategori Listesi</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kategori Adı</th>
                            <th>URL (Slug)</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kategoriler)): ?>
                            <tr><td colspan="4" style="text-align: center;">Henüz kategori eklenmemiş.</td></tr>
                        <?php else: ?>
                            <?php foreach($kategoriler as $kat): ?>
                                <tr>
                                    <td><strong>#<?php echo $kat['Id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($kat['Name']); ?></td>
                                    <td><code style="background: #f1f5f9; padding: 3px 6px; border-radius: 4px;"><?php echo htmlspecialchars($kat['Slug']); ?></code></td>
                                    <td>
                                        <a href="admin_kategoriler.php?sil_id=<?php echo $kat['Id']; ?>" class="btn-delete" onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?');">
                                            <i class="fa-solid fa-trash"></i> Sil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>