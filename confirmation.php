<?php

session_start();
include("config.php");


// ======================================
// VÉRIFIER LA CONNEXION
// ======================================

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}


// ======================================
// VÉRIFIER LE PAIEMENT
// ======================================

if (!isset($_SESSION["payment"])) {
    header("Location: home.php");
    exit();
}


$user_id = intval($_SESSION["user_id"]);

$payment = $_SESSION["payment"];

$order_id =
    intval($payment["order_id"] ?? 0);

$method =
    $payment["method_name"] ?? "Paiement mobile";

$payment_phone =
    $payment["phone"] ?? "";

$amount =
    floatval($payment["amount"] ?? 0);


// ======================================
// VÉRIFIER LA COMMANDE
// ======================================

$order_sql = "
    SELECT *
    FROM orders
    WHERE id = $order_id
    AND user_id = $user_id
    LIMIT 1
";

$order_result =
    mysqli_query($conn, $order_sql);


if (
    !$order_result ||
    mysqli_num_rows($order_result) == 0
) {

    unset($_SESSION["payment"]);

    header("Location: home.php");

    exit();
}


$order =
    mysqli_fetch_assoc($order_result);

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Commande confirmée - YK Shop</title>

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
    background: #f5f7fb;
    color: #172033;
}

.container {
    width: 90%;
    max-width: 700px;
    margin: 60px auto;
}

.confirmation {
    background: white;
    padding: 40px;
    border-radius: 22px;
    text-align: center;
    box-shadow: 0 10px 35px rgba(0,0,0,0.08);
}

.success-icon {
    width: 85px;
    height: 85px;
    margin: 0 auto 25px;
    border-radius: 50%;
    background: #fff7df;
    color: #d99a00;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
}

h1 {
    font-size: 30px;
    margin-bottom: 12px;
}

.intro {
    color: #667085;
    line-height: 1.6;
    margin-bottom: 30px;
}

.order-info {
    background: #f8fafc;
    border-radius: 15px;
    padding: 20px;
    text-align: left;
    margin-bottom: 25px;
}

.row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.row:last-child {
    border-bottom: none;
}

.row span:first-child {
    color: #667085;
}

.amount {
    color: #4f46e5;
    font-weight: bold;
}

.status {
    display: inline-block;
    padding: 9px 16px;
    border-radius: 30px;
    background: #fff7df;
    color: #9a6700;
    font-weight: bold;
}

.buttons {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.btn {
    flex: 1;
    padding: 14px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
}

.home {
    background: #4f46e5;
    color: white;
}

.orders {
    background: #eef2ff;
    color: #4f46e5;
}

.note {
    margin-top: 25px;
    color: #777;
    font-size: 13px;
    line-height: 1.6;
}

@media (max-width: 600px) {

    .confirmation {
        padding: 25px;
    }

    h1 {
        font-size: 25px;
    }

    .row {
        flex-direction: column;
        gap: 5px;
    }

    .buttons {
        flex-direction: column;
    }
}

</style>

</head>

<body>

<?php include("header.php"); ?>


<div class="container">

<div class="confirmation">


<div class="success-icon">

<i class="fa-solid fa-clock"></i>

</div>


<h1>
Commande enregistrée !
</h1>


<p class="intro">

Merci pour votre commande sur
<strong>YK Shop</strong>.

Votre commande a bien été enregistrée.
Le paiement est actuellement en attente de confirmation.

</p>


<div class="order-info">


<div class="row">

<span>
Numéro de commande
</span>

<strong>
#<?php echo $order_id; ?>
</strong>

</div>


<div class="row">

<span>
Montant
</span>

<strong class="amount">

<?php

echo number_format(
    $amount,
    0,
    ",",
    " "
);

?>

FCFA

</strong>

</div>


<div class="row">

<span>
Moyen de paiement
</span>

<strong>
<?php echo htmlspecialchars($method); ?>
</strong>

</div>


<div class="row">

<span>
Numéro de paiement
</span>

<strong>
<?php echo htmlspecialchars($payment_phone); ?>
</strong>

</div>


<div class="row">

<span>
Statut
</span>

<strong class="status">
En attente
</strong>

</div>


</div>


<div class="buttons">

<a
href="home.php"
class="btn home">

<i class="fa-solid fa-house"></i>

Accueil

</a>


<a
href="orders.php"
class="btn orders">

<i class="fa-solid fa-box"></i>

Mes commandes

</a>

</div>


<p class="note">

<i class="fa-solid fa-circle-info"></i>

Votre commande est enregistrée dans notre système.
Le statut passera à « payé » uniquement après confirmation réelle du paiement.

</p>


</div>

</div>

</body>

</html>