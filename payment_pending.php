<?php
session_start();
include("config.php");

// Vérifier la connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Vérifier le paiement
if (!isset($_SESSION["payment"]) || !isset($_SESSION["checkout"])) {
    header("Location: checkout.php");
    exit();
}

$payment = $_SESSION["payment"];
$checkout = $_SESSION["checkout"];

$payment_method = $payment["method"];
$payment_phone = $payment["phone"];
$total = $checkout["total"];

$payment_names = [
    "wave" => "Wave",
    "orange" => "Orange Money",
    "mtn" => "MTN Mobile Money",
    "moov" => "Moov Money"
];

$payment_name = $payment_names[$payment_method] ?? "Paiement mobile";
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Paiement en attente - YK Shop</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

body {
    min-height: 100vh;
    background: #f5f7fb;
    color: #1e293b;
}

/* HEADER */

header {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    padding: 18px 6%;

    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 4px 15px rgba(0,0,0,.15);
}

.logo {
    font-size: 25px;
    font-weight: bold;
}

nav {
    display: flex;
    gap: 22px;
}

nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

nav a:hover {
    opacity: .8;
}

/* CONTENU */

.container {
    width: 90%;
    max-width: 700px;
    margin: 60px auto;
}

/* CARTE */

.pending-box {
    background: white;
    border-radius: 22px;
    padding: 45px;
    text-align: center;

    box-shadow: 0 10px 35px rgba(0,0,0,.10);
}

/* ICONE */

.pending-icon {
    width: 90px;
    height: 90px;

    margin: 0 auto 25px;

    border-radius: 50%;

    background: #fef3c7;
    color: #f59e0b;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 42px;

    animation: pulse 1.5s infinite;
}

@keyframes pulse {

    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.08);
    }

    100% {
        transform: scale(1);
    }
}

/* TITRE */

h1 {
    font-size: 30px;
    margin-bottom: 12px;
}

.subtitle {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 30px;
}

/* INFORMATIONS */

.info {
    background: #f8fafc;
    border-radius: 15px;
    padding: 22px;
    text-align: left;
    margin-bottom: 25px;
}

.info-row {
    display: flex;
    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row span {
    color: #64748b;
}

.info-row strong {
    color: #1e293b;
}

.amount {
    color: #4f46e5 !important;
    font-size: 20px;
}

/* MESSAGE */

.warning {
    background: #fff7ed;
    color: #9a3412;

    border: 1px solid #fed7aa;

    border-radius: 12px;

    padding: 16px;

    line-height: 1.6;

    margin-bottom: 25px;
}

/* BOUTONS */

.buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    width: 100%;

    padding: 15px;

    border-radius: 11px;

    text-decoration: none;

    font-weight: bold;

    transition: .3s;
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(79,70,229,.30);
}

.btn-secondary {
    background: #f1f5f9;
    color: #334155;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

/* SECURITE */

.security {
    margin-top: 25px;

    color: #64748b;

    font-size: 13px;

    line-height: 1.6;
}

/* MOBILE */

@media (max-width: 600px) {

    header {
        flex-direction: column;
        gap: 15px;
    }

    nav {
        flex-wrap: wrap;
        justify-content: center;
        gap: 14px;
    }

    .pending-box {
        padding: 25px 20px;
    }

    h1 {
        font-size: 24px;
    }

    .info-row {
        flex-direction: column;
        gap: 5px;
    }
}

</style>

</head>

<body>

<?php include("header.php"); ?>


<div class="container">

    <div class="pending-box">

        <div class="pending-icon">

            <i class="fa-solid fa-clock"></i>

        </div>


        <h1>
            Paiement en attente
        </h1>


        <p class="subtitle">

            Votre demande de paiement a bien été enregistrée.

            <br>

            Nous attendons maintenant la confirmation du paiement.

        </p>


        <div class="info">

            <div class="info-row">

                <span>
                    Moyen de paiement
                </span>

                <strong>
                    <?php echo htmlspecialchars($payment_name); ?>
                </strong>

            </div>


            <div class="info-row">

                <span>
                    Numéro de paiement
                </span>

                <strong>
                    N<?php echo htmlspecialchars($payment_phone); ?>
                </strong>

            </div>


            <div class="info-row">

                <span>
                    Montant
                </span>

                <strong class="amount">

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

        </div>


        <div class="warning">

            <i class="fa-solid fa-circle-info"></i>

            <strong>
                Important
            </strong>

            <br>

            Cette page est actuellement une étape de test.

            Le paiement réel sera connecté à l'API du moyen
            de paiement choisi avant la mise en production.

        </div>


        <div class="buttons">

    <a href="confirm_order.php" class="btn btn-primary">

        <i class="fa-solid fa-check"></i>

        Confirmer la commande

    </a>

    <a href="cart.php" class="btn btn-secondary">

        <i class="fa-solid fa-cart-shopping"></i>

        Retour au panier

    </a>

</div>


            <a href="home.php" class="btn btn-primary">

                <i class="fa-solid fa-house"></i>

                Retour à l'accueil

            </a>

        </div>


        <p class="security">

            <i class="fa-solid fa-shield-halved"></i>

            Ne communiquez jamais votre code PIN Mobile Money à personne.

        </p>

    </div>

</div>

</body>

</html>