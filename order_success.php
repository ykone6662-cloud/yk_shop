<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION["order_success"])) {
    header("Location: home.php");
    exit();
}

$order = $_SESSION["order_success"];

$order_id = intval($order["order_id"]);
$total = floatval($order["total"]);

$payment_names = [
    "wave" => "Wave",
    "orange" => "Orange Money",
    "mtn" => "MTN Mobile Money",
    "moov" => "Moov Money"
];

$payment_method = $order["payment_method"];

$payment_name =
    $payment_names[$payment_method]
    ?? "Paiement mobile";

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Commande confirmée - YK Shop
</title>

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

    background: linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color: white;

    padding: 18px 6%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 4px 15px rgba(0,0,0,.15);
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


/* CONTENEUR */

.container {

    width: 90%;

    max-width: 700px;

    margin: 60px auto;
}


/* CARTE */

.success-box {

    background: white;

    border-radius: 22px;

    padding: 45px;

    text-align: center;

    box-shadow:
        0 10px 35px rgba(0,0,0,.10);
}


/* ICON */

.success-icon {

    width: 90px;

    height: 90px;

    margin: 0 auto 25px;

    border-radius: 50%;

    background: #dcfce7;

    color: #16a34a;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 42px;
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


/* INFOS */

.info {

    background: #f8fafc;

    border-radius: 15px;

    padding: 20px;

    text-align: left;

    margin-bottom: 25px;
}

.info-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 12px 0;

    border-bottom:
        1px solid #e2e8f0;
}

.info-row:last-child {

    border-bottom: none;
}

.info-row span {

    color: #64748b;
}

.info-row strong {

    text-align: right;
}

.amount {

    color: #4f46e5;

    font-size: 20px;
}


/* STATUT */

.status {

    display: inline-block;

    padding: 9px 18px;

    border-radius: 30px;

    background: #fef3c7;

    color: #92400e;

    font-weight: bold;

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

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #7c3aed
        );

    color: white;
}

.btn-primary:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(79,70,229,.30);
}

.btn-secondary {

    background: #f1f5f9;

    color: #334155;
}

.btn-secondary:hover {

    background: #e2e8f0;
}


/* MOBILE */

@media(max-width:600px) {

    header {

        flex-direction: column;

        gap: 15px;
    }

    nav {

        flex-wrap: wrap;

        justify-content: center;

        gap: 14px;
    }

    .success-box {

        padding: 25px 20px;
    }

    h1 {

        font-size: 24px;
    }

    .info-row {

        flex-direction: column;

        gap: 5px;
    }

    .info-row strong {

        text-align: left;
    }
}

</style>

</head>

<body>


<?php include("header.php"); ?>


<div class="container">


<div class="success-box">


<div class="success-icon">

    <i class="fa-solid fa-check"></i>

</div>


<h1>

Commande enregistrée !

</h1>


<p class="subtitle">

Merci pour votre commande.

Votre commande a bien été enregistrée.

</p>


<div class="status">

    <i class="fa-solid fa-clock"></i>

    Paiement en attente

</div>


<div class="info">


<div class="info-row">

    <span>

        Numéro de commande

    </span>

    <strong>

        N<?php echo $order_id; ?>

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


<div class="info-row">

    <span>

        Paiement

    </span>

    <strong>

        <?php

        echo htmlspecialchars(
            $payment_name
        );

        ?>

    </strong>

</div>


</div>


<div class="buttons">


<a href="orders.php"
class="btn btn-primary">

    <i class="fa-solid fa-box"></i>

    Voir mes commandes

</a>


<a href="home.php"
class="btn btn-secondary">

    <i class="fa-solid fa-house"></i>

    Continuer mes achats

</a>


</div>


</div>

</div>

</body>

</html>