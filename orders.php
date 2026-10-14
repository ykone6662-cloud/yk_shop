<?php

session_start();
include("config.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// Récupérer les commandes de l'utilisateur
$sql = "
    SELECT *
    FROM orders
    WHERE user_id = $user_id
    ORDER BY created_at DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erreur SQL : " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Mes commandes - YK Shop</title>

<!-- Font Awesome -->
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

/* =========================
   RESET
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================
   BODY
========================= */

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    color: #222;
}


/* =========================
   HEADER
========================= */

header {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);

    padding: 15px 6%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}


/* =========================
   LOGO
========================= */

.logo {
    display: flex;

    align-items: center;

    gap: 10px;

    color: white;

    text-decoration: none;

    font-size: 24px;

    font-weight: bold;

    white-space: nowrap;
}


.logo img {
    width: 45px;

    height: 45px;

    object-fit: contain;

    border-radius: 8px;
}


.logo:hover {
    opacity: 0.9;
}


/* =========================
   NAVIGATION
========================= */

nav {
    display: flex;

    align-items: center;

    gap: 22px;

    flex-wrap: wrap;

    justify-content: center;
}


nav a {
    color: white;

    text-decoration: none;

    font-size: 14px;

    font-weight: bold;

    display: flex;

    align-items: center;

    gap: 6px;

    transition: 0.3s;
}


nav a:hover {
    opacity: 0.8;

    transform: translateY(-1px);
}


/* =========================
   CONTENU
========================= */

.container {
    width: 90%;

    max-width: 1000px;

    margin: 45px auto;
}


/* =========================
   TITRE
========================= */

h1 {
    text-align: center;

    margin-bottom: 30px;

    color: #333;
}


/* =========================
   COMMANDE
========================= */

.order {
    background: white;

    padding: 25px;

    margin-bottom: 20px;

    border-radius: 14px;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.10);

    transition: 0.3s;
}


.order:hover {
    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.14);
}


/* =========================
   HEADER COMMANDE
========================= */

.order-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

    padding-bottom: 15px;

    border-bottom: 1px solid #eee;

    gap: 15px;
}


.order-number {
    font-size: 18px;

    font-weight: bold;

    color: #4f46e5;
}


.date {
    color: #777;

    font-size: 14px;
}


/* =========================
   INFORMATIONS COMMANDE
========================= */

.order-info {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;
}


.total {
    font-size: 20px;

    font-weight: bold;

    color: #4f46e5;
}


/* =========================
   STATUT
========================= */

.status {
    padding: 8px 14px;

    border-radius: 20px;

    background: #fef3c7;

    color: #92400e;

    font-size: 14px;

    font-weight: bold;
}


/* =========================
   BOUTON DETAILS
========================= */

.details {
    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 20px;

    padding: 11px 18px;

    border-radius: 8px;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    font-weight: bold;

    transition: 0.3s;
}


.details:hover {
    background: #372fc4;

    transform: translateY(-2px);
}


/* =========================
   AUCUNE COMMANDE
========================= */

.empty {
    background: white;

    text-align: center;

    padding: 60px 20px;

    border-radius: 14px;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.10);
}


.empty-icon {
    width: 90px;

    height: 90px;

    margin: 0 auto 20px;

    border-radius: 50%;

    background: linear-gradient(135deg, #4f46e5, #7c3aed);

    display: flex;

    align-items: center;

    justify-content: center;

    color: white;

    font-size: 40px;
}


.empty h2 {
    margin-bottom: 10px;
}


.empty p {
    color: #777;

    margin-bottom: 20px;
}


/* =========================
   BOUTON SHOP
========================= */

.shop {
    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 13px 22px;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-weight: bold;

    transition: 0.3s;
}


.shop:hover {
    background: rgb(47, 1, 230);

    transform: translateY(-2px);
}


/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

    header {

        flex-direction: column;

        padding: 15px 4%;

    }


    .logo {

        font-size: 22px;

    }


    .logo img {

        width: 40px;

        height: 40px;

    }


    nav {

        gap: 12px;

    }


    nav a {

        font-size: 13px;

    }


    .container {

        width: 94%;

        margin: 30px auto;

    }


    h1 {

        font-size: 25px;

    }


    .order {

        padding: 20px;

    }


    .order-header {

        flex-direction: column;

        align-items: flex-start;

    }


    .order-info {

        flex-direction: column;

        align-items: flex-start;

    }


    .total {

        font-size: 18px;

    }


    .details {

        width: 100%;

        justify-content: center;

    }

}

</style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<?php include("header.php"); ?>


<!-- =========================
     CONTENU
========================= -->

<div class="container">


<h1>
    <i class="fa-solid fa-box"></i>
    Mes commandes
</h1>


<?php if (mysqli_num_rows($result) > 0): ?>


    <?php while ($order = mysqli_fetch_assoc($result)): ?>


        <div class="order">


            <!-- HEADER COMMANDE -->

            <div class="order-header">


                <div class="order-number">

                    <i class="fa-solid fa-receipt"></i>

                    Commande N°<?php echo htmlspecialchars($order["id"]); ?>

                </div>


                <div class="date">

                    <i class="fa-regular fa-calendar"></i>

                    <?php echo htmlspecialchars($order["created_at"]); ?>

                </div>


            </div>


            <!-- INFORMATIONS -->

            <div class="order-info">


                <div class="total">

                    <?php

                    echo number_format(
                        $order["total"],
                        0,
                        ",",
                        " "
                    );

                    ?>

                    FCFA

                </div>


                <div class="status">

                    <?php

                    echo htmlspecialchars($order["status"]);

                    ?>

                </div>


            </div>


            <!-- DETAILS -->

            <a
                href="order_details.php?id=<?php echo urlencode($order["id"]); ?>"
                class="details"
            >

                <i class="fa-solid fa-eye"></i>

                Voir les détails

            </a>


        </div>


    <?php endwhile; ?>


<?php else: ?>


    <!-- AUCUNE COMMANDE -->

    <div class="empty">


        <div class="empty-icon">

            <i class="fa-solid fa-box-open"></i>

        </div>


        <h2>
            Aucune commande
        </h2>


        <p>
            Vous n'avez encore effectué aucune commande.
            Parcourez notre boutique et découvrez nos meilleures offres !
        </p>


        <a
            href="home.php"
            class="shop"
        >

            <i class="fa-solid fa-bag-shopping"></i>

            Découvrir les produits

        </a>


    </div>


<?php endif; ?>


</div>


</body>

</html>
