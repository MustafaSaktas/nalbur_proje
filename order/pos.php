<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece adminler girebilir
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: index.php");
    exit;
}

// Ürünleri veritabanından çekelim (Sadece aktif ve stoğu olanlar)
try {
    $urunlerSorgu = $db->query("SELECT Id, Name, Price, Stock, SKU FROM products WHERE IsActive = 1 AND Stock > 0 ORDER BY Name ASC");
    $urunler = $urunlerSorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hızlı Satış (POS) - Yönetim Paneli</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        .admin-content { margin-left: 260px; padding: 30px; display: flex; gap: 20px; }
        
        /* Sol Taraf: Ürünler */
        .pos-products { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .search-box { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 16px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; max-height: 70vh; overflow-y: auto; }
        .product-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; cursor: pointer; text-align: center; transition: 0.2s; background: #f8fafc; }
        .product-card:hover { border-color: #3b82f6; background: #eff6ff; }
        .product-name { font-size: 13px; color: #334155; margin-bottom: 5px; height: 38px; overflow: hidden; }
        .product-price { font-weight: bold; color: #16a34a; font-size: 15px; }
        .product-stock { font-size: 11px; color: #64748b; }

        /* Sağ Taraf: Sepet */
        .pos-cart { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .cart-table th { text-align: left; padding: 10px; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 14px; }
        .cart-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        .qty-btn { background: #e2e8f0; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .qty-btn:hover { background: #cbd5e1; }
        
        .cart-total { margin-top: auto; background: #f8fafc; padding: 15px; border-radius: 6px; text-align: right; font-size: 20px; font-weight: bold; color: #0f172a; border: 1px solid #e2e8f0; }
        .checkout-btn { width: 100%; padding: 15px; background: #16a34a; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 15px; transition: 0.2s; }
        .checkout-btn:hover { background: #15803d; }
    </style>
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-content">
        <div class="pos-products">
            <h3 style="margin-top:0; color:#1e293b;">Ürün Seçimi</h3>
            <input type="text" id="searchInput" class="search-box" placeholder="Ürün adı veya Barkod (SKU) ara..." onkeyup="filterProducts()">
            
            <div class="product-grid" id="productGrid">
                <?php foreach($urunler as $urun): ?>
                    <div class="product-card" data-name="<?php echo strtolower($urun['Name']); ?>" data-sku="<?php echo strtolower($urun['SKU']); ?>" onclick="addToCart(<?php echo $urun['Id']; ?>, '<?php echo addslashes($urun['Name']); ?>', <?php echo $urun['Price']; ?>, <?php echo $urun['Stock']; ?>)">
                        <div class="product-name"><?php echo htmlspecialchars($urun['Name']); ?></div>
                        <div class="product-price"><?php echo number_format($urun['Price'], 2, ',', '.'); ?> TL</div>
                        <div class="product-stock">Stok: <?php echo $urun['Stock']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="pos-cart">
            <h3 style="margin-top:0; color:#1e293b;">Kasa Sepeti</h3>
            <div style="flex-grow: 1; overflow-y: auto;">
                <table class="cart-table" id="cartTable">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Miktar</th>
                            <th>Fiyat</th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        </tbody>
                </table>
            </div>
            
            <div class="cart-total">
                Toplam: <span id="totalAmount">0.00</span> TL
            </div>
            <button class="checkout-btn" onclick="satisiTamamla()"><i class="fa-solid fa-check-double"></i> Satışı Tamamla</button>
        </div>
    </main>

    <script>
        let cart = [];

        // Ürün Arama Filtresi
        function filterProducts() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('product-card');
            for (let i = 0; i < cards.length; i++) {
                let name = cards[i].getAttribute('data-name');
                let sku = cards[i].getAttribute('data-sku');
                if (name.includes(input) || sku.includes(input)) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        // Sepete Ekle
        function addToCart(id, name, price, maxStock) {
            let existingItem = cart.find(item => item.productId === id);
            
            if (existingItem) {
                if(existingItem.quantity < maxStock) {
                    existingItem.quantity += 1;
                    existingItem.subtotal = existingItem.quantity * price;
                } else {
                    alert("Yetersiz Stok! Maksimum " + maxStock + " adet satabilirsiniz.");
                }
            } else {
                cart.push({
                    productId: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    subtotal: price,
                    maxStock: maxStock
                });
            }
            renderCart();
        }

        // Miktar Değiştirme
        function changeQty(id, delta) {
            let item = cart.find(item => item.productId === id);
            if (item) {
                let newQty = item.quantity + delta;
                if (newQty <= 0) {
                    cart = cart.filter(i => i.productId !== id); // Ürünü sil
                } else if (newQty > item.maxStock) {
                    alert("Yetersiz Stok!");
                } else {
                    item.quantity = newQty;
                    item.subtotal = item.quantity * item.price;
                }
                renderCart();
            }
        }

        // Sepeti Çiz
        function renderCart() {
            let tbody = document.getElementById('cartBody');
            tbody.innerHTML = '';
            let total = 0;

            cart.forEach(item => {
                total += item.subtotal;
                tbody.innerHTML += `
                    <tr>
                        <td>${item.name.substring(0,25)}...</td>
                        <td>
                            <button class="qty-btn" onclick="changeQty(${item.productId}, -1)">-</button>
                            ${item.quantity}
                            <button class="qty-btn" onclick="changeQty(${item.productId}, 1)">+</button>
                        </td>
                        <td>${item.subtotal.toFixed(2)} TL</td>
                    </tr>
                `;
            });

            document.getElementById('totalAmount').innerText = total.toFixed(2);
        }

        // Backend'e Gönder
        function satisiTamamla() {
            if (cart.length === 0) {
                alert("Sepet boş!");
                return;
            }

            let total = cart.reduce((sum, item) => sum + item.subtotal, 0);
            
            let dataPackage = {
                totalPrice: total,
                items: cart
            };

            fetch('pos_isle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataPackage)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("Satış Başarıyla Tamamlandı!\nSipariş No: " + data.order_id);
                    cart = []; // Sepeti boşalt
                    renderCart();
                    location.reload(); // Stokları güncellemek için sayfayı yenile
                } else {
                    alert("Hata: " + data.message);
                }
            })
            .catch(error => {
                console.error("Hata:", error);
                alert("Sunucu ile iletişim kurulamadı.");
            });
        }
    </script>
</body>
</html>