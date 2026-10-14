<?php
// header.php
?>

<!-- HEADER YK SHOP -->
<header class="main-header">

    <div class="header-container">

        <!-- LOGO -->
        <a href="home.php" class="brand">

            <img
                src="product_image.php?file=logo.png"
                alt="YK Shop"
                class="brand-logo"
            >

            <span class="brand-name">
                YK Shop
            </span>

        </a>


        <!-- MENU -->
        <nav class="main-nav">

            <a href="home.php">
                <i class="fa-solid fa-house"></i>
                <span>Accueil</span>
            </a>

            <a href="cart.php">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Panier</span>
            </a>

            <a href="orders.php">
                <i class="fa-solid fa-box-open"></i>
                <span>Commandes</span>
            </a>

            <a href="profile.php">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>

            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>

        </nav>

    </div>

</header>


<style>

/* ============================= */
/* HEADER YK SHOP                 */
/* ============================= */

.main-header {

    width: 100%;

    background: linear-gradient(
        135deg,
        #0798e8,
        #087df5
    );

    color: white;

    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.12);

    position: relative;

    z-index: 1000;
}


.header-container {

    width: 90%;

    max-width: 1200px;

    margin: auto;

    min-height: 88px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 30px;
}


/* ============================= */
/* LOGO                           */
/* ============================= */

.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    color: white;

    text-decoration: none;

    flex-shrink: 0;
}


.brand-logo {

    width: 64px;

    height: 64px;

    object-fit: contain;

    background: white;

    border-radius: 50%;

    padding: 3px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.15);
}


.brand-name {

    font-size: 25px;

    font-weight: 800;

    letter-spacing: -0.5px;
}


/* ============================= */
/* NAVIGATION                    */
/* ============================= */

.main-nav {

    display: flex;

    align-items: center;

    gap: 5px;
}


.main-nav a {

    display: flex;

    align-items: center;

    gap: 8px;

    color: white;

    text-decoration: none;

    font-size: 15px;

    font-weight: 700;

    padding: 12px 14px;

    border-radius: 10px;

    transition: all 0.25s ease;
}


.main-nav a:hover {

    background: rgba(255,255,255,0.18);

    transform: translateY(-1px);
}


.main-nav a i {

    font-size: 16px;
}


/* Déconnexion */

.logout-link:hover {

    background: rgba(255,255,255,0.22);
}


/* ============================= */
/* TABLETTE                      */
/* ============================= */

@media (max-width: 900px) {

    .header-container {

        width: 94%;

        gap: 10px;
    }

    .brand-name {

        font-size: 21px;
    }

    .brand-logo {

        width: 55px;

        height: 55px;
    }

    .main-nav {

        gap: 0;
    }

    .main-nav a {

        padding: 10px 8px;

        font-size: 13px;
    }

    .main-nav a i {

        font-size: 14px;
    }

}


/* ============================= */
/* MOBILE                       */
/* ============================= */

@media (max-width: 700px) {

    .header-container {

        min-height: auto;

        padding: 12px 0;

        flex-direction: column;

        gap: 12px;
    }


    .brand-logo {

        width: 58px;

        height: 58px;
    }


    .brand-name {

        font-size: 22px;
    }


    .main-nav {

        width: 100%;

        justify-content: center;

        flex-wrap: wrap;

        gap: 3px;
    }


    .main-nav a {

        padding: 9px 8px;

        font-size: 12px;
    }


    .main-nav a span {

        display: none;
    }


    .main-nav a i {

        font-size: 17px;
    }

}


/* ============================= */
/* TRÈS PETIT ÉCRAN              */
/* ============================= */

@media (max-width: 400px) {

    .main-nav {

        gap: 1px;
    }

    .main-nav a {

        padding: 8px 7px;
    }

}

</style>
