<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece admin yetkisi olanlar (Role 1) erişebilir [cite: 153]
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

try {
    // KULLANICI LİSTELEME SORGUSU 
    // Join [cite: 102, 127] ve Karakter Fonksiyonu (CONCAT, UPPER) [cite: 105, 130]
    // DÜZELTME: r.RoleName yerine r.Name kullanıldı. u.CreatedAt yerine u.CreateAt kullanıldı.
    $sql = "SELECT 
                u.Id, 
                CONCAT(UPPER(u.FName), ' ', UPPER(u.LName)) as FullName, 
                u.Email, 
                r.Name as RoleName, 
                u.CreateAt 
            FROM users u
            JOIN roles r ON u.RoleId = r.Id
            ORDER BY u.Id ASC";
            
    $sorgu = $db->query($sql);
    $kullanicilar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Hata durumunda mesajı gösterir (Teslimden önce bu satırı gizleyebilirsin)
    die("Veritabanı Hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Yönetimi - NalburDükkan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { margin-left: 260px; padding: 30px; background: #f8fafc; min-height: 100vh; }
        .user-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .modern-table { width: 100%; border-collapse: collapse; }
        .modern-table th { text-align: left; padding: 15px; background: #f1f5f9; color: #475569; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .modern-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }
        
        .role-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .role-admin { background: #dbeafe; color: #1e40af; }
        .role-customer { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-container">
        <div style="margin-bottom: 25px;">
            <h2 style="color: #1e293b;"><i class="fa-solid fa-users-gear" style="color: #ff6600;"></i> Kullanıcı ve Yetki Yönetimi</h2>
            <p style="color: #64748b;">Sistemde kayıtlı kullanıcıların rolleri ve bilgileri aşağıdadır.</p>
        </div>

        <div class="user-card">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad Soyad (Sorgu: CONCAT/UPPER)</th>
                        <th>E-Posta</th>
                        <th>Yetki Grubu</th>
                        <th>Kayıt Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($kullanicilar as $k): ?>
                    <tr>
                        <td>#<?php echo $k['Id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($k['FullName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($k['Email']); ?></td>
                        <td>
                            <?php 
                                // Rol ismine göre renkli rozet gösterimi
                                $role = $k['RoleName'];
                                $badgeClass = ($role == 'Admin') ? 'role-admin' : 'role-customer';
                                echo "<span class='role-badge $badgeClass'>$role</span>";
                            ?>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($k['CreateAt'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>