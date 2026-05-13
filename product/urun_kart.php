<div class="product-card">
    <div class="badge"><?php echo isset($is_cok_satan) ? 'Çok Satan' : 'Fırsat Ürünü'; ?></div>
    
    <a href="favori_islem.php?id=<?php echo $urun['Id']; ?>" class="fav-btn">
        <i class="fa-regular fa-heart"></i>
    </a>

    <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>">
        <div class="card-image">
            <img src="<?php echo $urun['ImagePath']; ?>" alt="Ürün">
        </div>
    </a>

    <div class="card-details">
        <span class="cargo-badge">Hızlı Kargo</span>
        <a href="urun-detay.php?id=<?php echo $urun['Id']; ?>" style="text-decoration:none; color:inherit;">
            <h3 class="product-title"><?php echo htmlspecialchars($urun['Name']); ?></h3>
        </a>
        
        <div class="price-container">
            <span class="new-price"><?php echo number_format($urun['Price'], 2, ',', '.'); ?> TL</span>
        </div>

        <form action="sepet.php" method="POST">
            <input type="hidden" name="urun_id" value="<?php echo $urun['Id']; ?>">
            <input type="hidden" name="islem" value="ekle">
            <button type="submit" class="add-to-cart-btn">Sepete Ekle</button>
        </form>
    </div>
</div>