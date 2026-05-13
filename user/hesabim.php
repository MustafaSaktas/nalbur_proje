    <?php
    session_start();
    require_once 'baglan.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: giris.php");
        exit;  
    }

    // Buraya ulaştıysak kullanıcı kesin giriş yapmıştır, isimleri değişkene alabiliriz.
    $name = mb_convert_case($_SESSION['user_name'],MB_CASE_TITLE,'UTF-8');
    require_once 'header.php';
    ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user .profile {
        display: flex;               /* İçindeki öğeleri esnek yapar */
        justify-content: center;     /* Yatay eksende tam ortalar */
        align-items: center;         /* Dikey eksende tam ortalar */
        background-color:gray ;   /* Belirlediğin arka plan rengi */
        height: 350px;               /* Yüksekliği kendine göre ayarlayabilirsin (300px profiller için genelde büyüktür) */
        border: 1px solid black;     /* Alanı görmek için çerçeve */
        margin-bottom: 15px;         /* İsimle arasına biraz boşluk bırakmak için */
    }

    /* Profil resminin (img) boyut ayarları */
    .user .profile img {
        max-width: 80%;             /* Resmin kutudan taşmasını engeller */
        max-height: 80%;            /* Resmin dikeyde kutudan taşmasını engeller */
        /* İsteğe bağlı: Resmin yuvarlak görünmesini istersen aşağıdaki satırı aktif edebilirsin */
        /* border-radius: 50%; */
    }

  .profile-button {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: black;
        border: 2px solid #e11d61;
        border-radius: 8px;
        padding: 10px 15px;
        transition: 0.3s ease;
        /* DİKKAT: Genişlik (width) ve margin-auto kodlarını buradan çıkardık 
       Böylece Navbar'daki butonlar kendi içeriği kadar yer kaplayacak. */
    }

/* İkon ile yazı arasına biraz mesafe koyalım ve ikonu renklendirelim */
    .profile-button i {
        font-size: 22px;             /* İkon boyutu */
        margin-right: 15px;          /* İkon ile yazı arasındaki boşluk */
        color: #e11d61;              /* İkonun rengi */
    }

    /* YAZIYI KALINLAŞTIRALIM (İsteğe bağlı) */
    .profile-button .title {
        font-weight: bold;
        font-size: 16px;
    }
    .user .profile-button {
        width: 250px;           /* Sadece profil sayfasındaki butonları 250px yapar */
        margin-top: 15px;       /* Üstten boşluk verir */
        margin-left: auto;      /* Yatayda tam merkeze alır */
        margin-right: auto;     /* Yatayda tam merkeze alır */
    }

   .user .name {
    text-align: center; 
    margin-bottom: 25px; /* Altındaki butonlarla biraz mesafe bırakır */
    }

/* Sadece "HESABIM" (span) yazısının ayarları */
    .user .name span {
        display: block;      /* Flex yerine Block yaptık, tüm satırı kaplamasını sağladık */
        font-size: 30px;     /* Tavsiye: 50px mobilde ekrandan taşabilir, 30-35px daha şık durur */
        color: #333;
        font-weight: 600;
    }

    /* İsmin (Rahmi Tekin yazan kısmın) ayarları - İsteğe bağlı */
    .user .name strong {
        font-size: 20px;
        color: #e11d61;      /* İsmi de sitenin konsept rengi yapabilirsin */
    }

</style>



    <div class="user">
        <div class="profile">
            <img src="fotolar/varsayilan-profil.png" alt="Profil Resmi">
        </div>
        <div class="name">
            <span class="title">HESABIM <br></span>
            <strong>
            <?php echo $name;?>
            </strong>
        
        </div>
        <a href="siparisler.php" class="profile-button">
                <i class="fa-solid fa-box-open"></i>
                <div class="action-text">
                    <span class="title">Siparişlerim</span>
                    
                </div>
            </a>

        <a href="kullanicibilgileri.php" class="profile-button">
                <i class="fa-solid fa-user-pen"></i>
                <div class="action-text">
                    <span class="title">Kullanıcı Bilgilerim</span>
                    
                </div>
            </a>




    </div>




