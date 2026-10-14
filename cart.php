<?php
session_start();
include("config.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// Récupérer les produits du panier
$sql = "
    SELECT
        cart.id AS cart_id,
        cart.quantity,
        products.id AS product_id,
        products.name,
        products.description,
        products.price,
        products.image,
        products.category
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = $user_id
    ORDER BY cart.id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erreur SQL : " . mysqli_error($conn));
}

$total = 0;
$total_items = 0;

// Calcul du nombre total d'articles
mysqli_data_seek($result, 0);

while ($row = mysqli_fetch_assoc($result)) {
    $total_items += intval($row["quantity"]);
}

// Revenir au début du résultat
mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Mon panier - YK Shop</title>

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
            font-family: Arial, Helvetica, sans-serif;
        }


        /* =========================
           BODY
        ========================= */

        body {
            background: #f5f8fc;
            color: #172033;
            min-height: 100vh;
        }


        /* =========================
           HEADER
        ========================= */

        header {
            background: linear-gradient(
                135deg,
                #2b07e0,
                #087cf0
            );

            min-height: 78px;

            padding: 10px 6%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            box-shadow:
                0 5px 20px rgba(0, 90, 180, 0.20);

            position: sticky;
            top: 0;
            z-index: 1000;
        }


        /* LOGO */

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;

            text-decoration: none;
            color: white;
        }

        .logo img {
            width: 58px;
            height: 58px;

            object-fit: contain;

            background: white;
            border-radius: 50%;

            padding: 3px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.15);
        }

        .logo span {
            font-size: 25px;
            font-weight: 800;
        }


        /* =========================
           NAVIGATION
        ========================= */

        nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav a {
            color: white;
            text-decoration: none;

            font-size: 14px;
            font-weight: bold;

            padding: 10px 13px;

            border-radius: 10px;

            display: flex;
            align-items: center;
            gap: 7px;

            transition: all 0.3s ease;
        }

        nav a:hover {
            background: rgba(255,255,255,0.18);
            transform: translateY(-2px);
        }

        nav a.active {
            background: rgba(255,255,255,0.22);
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 90%;
            max-width: 1150px;

            margin: 45px auto;
        }


        /* =========================
           TITRE
        ========================= */

        .title {
            margin-bottom: 30px;
        }

        .title h1 {
            font-size: 34px;
            margin-bottom: 8px;

            display: flex;
            align-items: center;
            gap: 12px;
        }

        .title h1 i {
            color: #490fd1;
        }

        .title p {
            color: #718096;
            font-size: 15px;
        }


        /* =========================
           PANIER
        ========================= */

        .cart-container {
            display: grid;

            grid-template-columns: 1fr 350px;

            gap: 28px;

            align-items: start;
        }


        /* =========================
           PRODUITS
        ========================= */

        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }


        .cart-item {
            background: white;

            border-radius: 18px;

            padding: 18px;

            display: flex;
            align-items: center;

            gap: 20px;

            border: 1px solid #edf1f7;

            box-shadow:
                0 8px 25px rgba(30, 60, 90, 0.07);

            transition: all 0.3s ease;
        }

        .cart-item:hover {
            transform: translateY(-3px);

            box-shadow:
                0 12px 30px rgba(30, 60, 90, 0.12);
        }


        /* =========================
           IMAGE
        ========================= */

        .product-image {
            width: 135px;
            height: 135px;

            background: #f7fafc;

            border-radius: 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            flex-shrink: 0;
        }

        .product-image img {
            width: 100%;
            height: 100%;

            object-fit: cover;

            transition: transform 0.4s ease;
        }

        .cart-item:hover .product-image img {
            transform: scale(1.05);
        }


        /* =========================
           INFORMATIONS
        ========================= */

        .product-info {
            flex: 1;
        }


        .category {
            display: inline-block;

            background: #e0f4ff;
            color: #0878bd;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 11px;
            font-weight: bold;

            margin-bottom: 8px;
        }


        .product-info h3 {
            font-size: 20px;

            margin-bottom: 7px;

            color: #172033;
        }


        .product-info p {
            color: #718096;

            font-size: 14px;

            line-height: 1.5;

            margin-bottom: 10px;
        }


        .quantity {
            display: inline-flex;

            align-items: center;

            background: #f3f7fb;

            padding: 7px 12px;

            border-radius: 8px;

            font-size: 13px;

            margin-bottom: 10px;
        }


        .item-price {
            color: #087fe0;

            font-weight: 800;

            font-size: 19px;
        }


        /* =========================
           SUPPRIMER
        ========================= */

        .delete-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            padding: 11px 14px;

            background: #fff1f1;
            color: #e33434;

            border: 1px solid #ffd5d5;

            border-radius: 10px;

            text-decoration: none;

            font-weight: bold;
            font-size: 13px;

            transition: all 0.3s ease;
        }


        .delete-btn:hover {
            background: #e33434;
            color: white;

            transform: translateY(-2px);

            box-shadow:
                0 6px 15px rgba(227,52,52,0.20);
        }


        /* =========================
           RÉSUMÉ
        ========================= */

        .summary {
            background: white;

            border-radius: 20px;

            padding: 28px;

            border: 1px solid #edf1f7;

            box-shadow:
                0 10px 30px rgba(30,60,90,0.08);

            position: sticky;
            top: 100px;
        }


        .summary h2 {
            font-size: 22px;

            margin-bottom: 25px;

            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary h2 i {
            color: #078fe0;
        }


        .summary-line {
            display: flex;

            justify-content: space-between;

            margin-bottom: 17px;

            color: #667085;

            font-size: 15px;
        }


        .summary-line strong {
            color: #172033;
        }


        .summary-total {
            border-top: 1px solid #e5eaf0;

            padding-top: 20px;
            margin-top: 20px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            font-size: 22px;

            font-weight: 800;
        }


        .summary-total span:last-child {
            color: #087fe0;
        }


        /* =========================
           BOUTON COMMANDER
        ========================= */

        .checkout-btn {
            display: flex;

            justify-content: center;
            align-items: center;

            gap: 9px;

            width: 100%;

            padding: 15px;

            margin-top: 25px;

            background: linear-gradient(
                135deg,
                #078fe0,
                #0875ef
            );

            color: white;

            text-decoration: none;

            border-radius: 11px;

            font-weight: bold;

            transition: all 0.3s ease;

            box-shadow:
                0 7px 18px rgba(8,125,230,0.20);
        }


        .checkout-btn:hover {
            transform: translateY(-3px);

            box-shadow:
                0 12px 25px rgba(8,125,230,0.30);
        }


        /* =========================
           CONTINUER ACHATS
        ========================= */

        .continue-btn {
            display: flex;

            justify-content: center;
            align-items: center;

            gap: 8px;

            width: 100%;

            padding: 14px;

            margin-top: 12px;

            background: #f2f6fa;

            color: #344054;

            text-decoration: none;

            border-radius: 11px;

            font-weight: bold;

            transition: all 0.3s ease;
        }


        .continue-btn:hover {
            background: #e4edf5;

            transform: translateY(-2px);
        }


        /* =========================
           PANIER VIDE
        ========================= */

        .empty-cart {
            background: white;

            border-radius: 22px;

            padding: 85px 25px;

            text-align: center;

            border: 1px solid #edf1f7;

            box-shadow:
                0 10px 30px rgba(30,60,90,0.08);
        }


        .empty-cart i {
            font-size: 70px;

            color: #078fe0;

            margin-bottom: 22px;
        }


        .empty-cart h2 {
            font-size: 25px;

            margin-bottom: 10px;
        }


        .empty-cart p {
            color: #718096;

            margin-bottom: 28px;
        }


        .empty-cart a {
            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 14px 24px;

            background: linear-gradient(
                135deg,
                #078fe0,
                #0875ef
            );

            color: white;

            text-decoration: none;

            border-radius: 11px;

            font-weight: bold;

            transition: 0.3s;
        }


        .empty-cart a:hover {
            transform: translateY(-3px);

            box-shadow:
                0 10px 20px rgba(8,125,230,0.25);
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            margin-top: 70px;

            background: #101828;

            color: #d0d5dd;

            text-align: center;

            padding: 28px 15px;

            font-size: 14px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            header {
                flex-direction: column;
                gap: 12px;
            }

            nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .cart-container {
                grid-template-columns: 1fr;
            }

            .summary {
                position: static;
            }
        }


        @media (max-width: 600px) {

            .container {
                width: 94%;
                margin: 30px auto;
            }

            .title h1 {
                font-size: 27px;
            }

            .cart-item {
                flex-direction: column;
                align-items: stretch;
            }

            .product-image {
                width: 100%;
                height: 220px;
            }

            .delete-btn {
                width: 100%;
            }

            .logo span {
                font-size: 21px;
            }

            nav {
                gap: 3px;
            }

            nav a {
                font-size: 12px;
                padding: 8px;
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


    <div class="title">

        <h1>

            <i class="fa-solid fa-cart-shopping"></i>

            Mon panier

        </h1>

        <p>
            Vérifiez vos produits avant de passer votre commande.
        </p>

    </div>


    <?php if (mysqli_num_rows($result) > 0): ?>


        <div class="cart-container">


            <!-- =========================
                 PRODUITS
            ========================= -->

            <div class="cart-items">


                <?php while ($item = mysqli_fetch_assoc($result)): ?>

                    <?php

                    $subtotal =
                        $item["price"] * $item["quantity"];

                    $total += $subtotal;

                    ?>


                    <div class="cart-item">


                        <!-- IMAGE -->

                        <div class="product-image">

                            <img
                                src="images/<?php
                                echo htmlspecialchars(
                                    $item["image"]
                                );
                                ?>"
                                alt="<?php
                                echo htmlspecialchars(
                                    $item["name"]
                                );
                                ?>"
                            >

                        </div>


                        <!-- INFORMATIONS -->

                        <div class="product-info">


                            <span class="category">

                                <?php
                                echo htmlspecialchars(
                                    $item["category"]
                                );
                                ?>

                            </span>


                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $item["name"]
                                );
                                ?>

                            </h3>


                            <p>

                                <?php

                                $description =
                                    $item["description"];

                                echo htmlspecialchars(
                                    mb_substr(
                                        $description,
                                        0,
                                        80
                                    )
                                );

                                if (
                                    strlen($description) > 80
                                ) {
                                    echo "...";
                                }

                                ?>

                            </p>


                            <div class="quantity">

                                <i
                                    class="fa-solid fa-layer-group"
                                    style="margin-right:7px;"
                                ></i>

                                Quantité :

                                <strong style="margin-left:5px;">

                                    <?php
                                    echo intval(
                                        $item["quantity"]
                                    );
                                    ?>

                                </strong>

                            </div>


                            <div class="item-price">

                                <?php

                                echo number_format(
                                    $item["price"],
                                    0,
                                    ",",
                                    " "
                                );

                                ?>

                                FCFA / unité

                            </div>


                        </div>


                        <!-- SUPPRIMER -->

                        <div>

                            <a
                                href="remove_from_cart.php?id=<?php
                                echo $item["cart_id"];
                                ?>"
                                class="delete-btn"

                                onclick="
                                return confirm(
                                'Voulez-vous vraiment supprimer ce produit du panier ?'
                                );
                                "
                            >

                                <i
                                    class="fa-solid fa-trash"
                                ></i>

                                Supprimer

                            </a>

                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


            <!-- =========================
                 RÉSUMÉ
            ========================= -->

            <div class="summary">


                <h2>

                    <i
                        class="fa-solid fa-receipt"
                    ></i>

                    Résumé du panier

                </h2>


                <div class="summary-line">

                    <span>
                        Articles
                    </span>

                    <strong>

                        <?php
                        echo $total_items;
                        ?>

                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Sous-total
                    </span>

                    <strong>

                        <?php

                        echo number_format(
                            $total,
                            0,
                            ",",
                            " "
                        );

                        ?>

                        FCFA

                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Livraison
                    </span>

                    <span>
                        À définir
                    </span>

                </div>


                <div class="summary-total">

                    <span>
                        Total
                    </span>

                    <span>

                        <?php

                        echo number_format(
                            $total,
                            0,
                            ",",
                            " "
                        );

                        ?>

                        FCFA

                    </span>

                </div>


                <!-- COMMANDER -->

                <a
                    href="checkout.php"
                    class="checkout-btn"
                >

                    <i
                        class="fa-solid fa-credit-card"
                    ></i>

                    Passer la commande

                </a>


                <!-- CONTINUER -->

                <a
                    href="home.php"
                    class="continue-btn"
                >

                    <i
                        class="fa-solid fa-arrow-left"
                    ></i>

                    Continuer mes achats

                </a>


            </div>


        </div>


    <?php else: ?>


        <!-- =========================
             PANIER VIDE
        ========================= -->

        <div class="empty-cart">


            <i
                class="fa-solid fa-cart-shopping"
            ></i>


            <h2>
                Votre panier est vide
            </h2>


            <p>
                Vous n'avez encore ajouté aucun produit.
            </p>


            <a href="home.php">

                <i
                    class="fa-solid fa-store"
                ></i>

                Découvrir les produits

            </a>


        </div>


    <?php endif; ?>


</div>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <p>

        © <?php echo date("Y"); ?>

        YK Shop — Tous droits réservés.

    </p>

</footer>


</body>

</html>