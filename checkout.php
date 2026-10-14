 <?php

session_start();
include("config.php");

// Vérifier la connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// Récupérer les informations du client
$user_sql = "
    SELECT *
    FROM users
    WHERE id = $user_id
";

$user_result = mysqli_query($conn, $user_sql);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$user = mysqli_fetch_assoc($user_result);


// Récupérer le panier
$cart_sql = "
    SELECT
        cart.id AS cart_id,
        cart.quantity,
        products.id AS product_id,
        products.name,
        products.price,
        products.image
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = $user_id
    ORDER BY cart.id DESC
";

$cart_result = mysqli_query($conn, $cart_sql);

if (!$cart_result) {
    die("Erreur SQL : " . mysqli_error($conn));
}


// Vérifier si le panier est vide
if (mysqli_num_rows($cart_result) == 0) {
    header("Location: cart.php");
    exit();
}


// Calculer le total
$total = 0;
$total_items = 0;

$cart_items = [];

while ($item = mysqli_fetch_assoc($cart_result)) {

    $subtotal = $item["price"] * $item["quantity"];

    $total += $subtotal;

    $total_items += $item["quantity"];

    $cart_items[] = $item;
}


// Traitement du formulaire
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $payment_method = $_POST["payment_method"] ?? "";


    // Vérification
    if (
        empty($fullname) ||
        empty($phone) ||
        empty($address) ||
        empty($city) ||
        empty($payment_method)
    ) {

        $error = "Veuillez remplir tous les champs.";

    } elseif (
        !in_array(
            $payment_method,
            ["wave", "orange", "mtn", "moov"]
        )
    ) {

        $error = "Moyen de paiement invalide.";

    } else {

        /*
         * Pour l'instant nous enregistrons les informations
         * dans la session.
         *
         * Le vrai paiement sera connecté ensuite
         * avec l'API du moyen de paiement choisi.
         */

        $_SESSION["checkout"] = [

            "fullname" => $fullname,

            "phone" => $phone,

            "address" => $address,

            "city" => $city,

            "payment_method" => $payment_method,

            "total" => $total

        ];


        header("Location: payment.php");

        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Finaliser la commande - YK Shop</title>


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

    color: #222;
}


/* =========================
HEADER
========================= */

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
        0 4px 15px rgba(0,0,0,0.15);
}


.logo {

    font-size: 25px;

    font-weight: bold;
}


nav {

    display: flex;

    gap: 22px;

    align-items: center;
}


nav a {

    color: white;

    text-decoration: none;

    font-weight: bold;

    transition: 0.3s;
}


nav a:hover {

    opacity: 0.8;
}


/* =========================
CONTENEUR
========================= */

.container {

    width: 90%;

    max-width: 1150px;

    margin: 45px auto;
}


.title {

    margin-bottom: 30px;
}


.title h1 {

    font-size: 32px;

    margin-bottom: 8px;
}


.title p {

    color: #777;
}


/* =========================
ERREUR
========================= */

.error {

    background: #fee2e2;

    color: #b91c1c;

    border: 1px solid #fecaca;

    padding: 14px;

    border-radius: 10px;

    margin-bottom: 20px;

    font-weight: bold;
}


/* =========================
LAYOUT
========================= */

.checkout-grid {

    display: grid;

    grid-template-columns: 1fr 380px;

    gap: 25px;

    align-items: start;
}


/* =========================
FORMULAIRE
========================= */

.checkout-box {

    background: white;

    padding: 30px;

    border-radius: 18px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.08);
}


.checkout-box h2 {

    margin-bottom: 25px;

    font-size: 22px;
}


.form-group {

    margin-bottom: 20px;
}


.form-group label {

    display: block;

    margin-bottom: 8px;

    font-weight: bold;
}


.form-group input,
.form-group textarea {

    width: 100%;

    padding: 14px;

    border: 1px solid #d1d5db;

    border-radius: 10px;

    font-size: 15px;

    outline: none;

    transition: 0.3s;
}


.form-group textarea {

    min-height: 100px;

    resize: vertical;
}


.form-group input:focus,
.form-group textarea:focus {

    border-color: #4f46e5;

    box-shadow:
        0 0 0 3px rgba(79,70,229,0.10);
}


/* =========================
PAIEMENT
========================= */

.payment-title {

    margin-top: 30px;

    margin-bottom: 15px;

    font-size: 20px;
}


.payment-options {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 12px;
}


.payment-option {

    position: relative;
}


.payment-option input {

    position: absolute;

    opacity: 0;
}


.payment-option label {

    display: flex;

    align-items: center;

    justify-content: center;

    min-height: 65px;

    padding: 12px;

    border: 2px solid #e5e7eb;

    border-radius: 12px;

    cursor: pointer;

    font-weight: bold;

    transition: 0.3s;

    text-align: center;
}


.payment-option label:hover {

    border-color: #4f46e5;

    transform: translateY(-2px);
}


.payment-option input:checked + label {

    border-color: #4f46e5;

    background: #eef2ff;

    color: #4f46e5;
}


/* Couleurs visuelles */

.wave label {

    color: #087cff;
}


.orange label {

    color: #f97316;
}


.mtn label {

    color: #111827;
}


.moov label {

    color: #16a34a;
}


/* =========================
BOUTON
========================= */

.submit-btn {

    width: 100%;

    border: none;

    margin-top: 25px;

    padding: 16px;

    border-radius: 10px;

    background: linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;
}


.submit-btn:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(79,70,229,0.30);
}


/* =========================
RÉSUMÉ
========================= */

.summary {

    background: white;

    padding: 25px;

    border-radius: 18px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.08);

    position: sticky;

    top: 20px;
}


.summary h2 {

    margin-bottom: 20px;
}


.summary-item {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid #eee;
}


.summary-image {

    width: 65px;

    height: 65px;

    background: #f8fafc;

    border-radius: 8px;

    overflow: hidden;

    flex-shrink: 0;
}


.summary-image img {

    width: 100%;

    height: 100%;

    object-fit: contain;
}


.summary-info {

    flex: 1;
}


.summary-info h3 {

    font-size: 14px;

    margin-bottom: 5px;
}


.summary-info p {

    color: #777;

    font-size: 13px;
}


.summary-price {

    font-weight: bold;

    color: #4f46e5;

    font-size: 14px;
}


.total {

    display: flex;

    justify-content: space-between;

    margin-top: 20px;

    padding-top: 18px;

    border-top: 2px solid #eee;

    font-size: 21px;

    font-weight: bold;
}


.total span:last-child {

    color: #4f46e5;
}


.back {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 8px;

    margin-top: 15px;

    color: #4f46e5;

    text-decoration: none;

    font-weight: bold;
}


/* =========================
MOBILE
========================= */

@media (max-width: 850px) {

    header {

        flex-direction: column;

        gap: 15px;
    }

    nav {

        flex-wrap: wrap;

        justify-content: center;

        gap: 14px;
    }

    .checkout-grid {

        grid-template-columns: 1fr;
    }

    .summary {

        position: static;
    }
}


@media (max-width: 500px) {

    .payment-options {

        grid-template-columns: 1fr;
    }

    .checkout-box {

        padding: 20px;
    }

}

</style>

</head>


<body>


<?php include("header.php"); ?>


<!-- CONTENU -->

<div class="container">


<div class="title">

<h1>
Finaliser ma commande
</h1>

<p>
Entrez vos informations de livraison et choisissez votre moyen de paiement.
</p>

</div>


<?php if (!empty($error)): ?>

<div class="error">

<i class="fa-solid fa-circle-exclamation"></i>

<?php echo htmlspecialchars($error); ?>

</div>

<?php endif; ?>


<div class="checkout-grid">


<!-- =========================
FORMULAIRE
========================= -->

<div class="checkout-box">


<h2>
<i class="fa-solid fa-location-dot"></i>

Informations de livraison
</h2>


<form method="POST">


<div class="form-group">

<label for="fullname">

Nom complet

</label>

<input
type="text"
id="fullname"
name="fullname"
value="<?php echo htmlspecialchars($user["fullname"] ?? ""); ?>"
placeholder="Ex : Yaya Koné"
required
>

</div>


<div class="form-group">

<label for="phone">

Numéro de téléphone

</label>

<input
type="tel"
id="phone"
name="phone"
value="<?php echo htmlspecialchars($user["phone"] ?? ""); ?>"
placeholder="Ex : 07 00 00 00 00"
required
>

</div>


<div class="form-group">

<label for="address">

Adresse de livraison

</label>

<textarea
id="address"
name="address"
placeholder="Ex : Abobo, près de..."
required
></textarea>

</div>


<div class="form-group">

<label for="city">

Ville

</label>

<input
type="text"
id="city"
name="city"
placeholder="Ex : Abidjan"
required
>

</div>


<h2 class="payment-title">

<i class="fa-solid fa-credit-card"></i>

Moyen de paiement

</h2>


<div class="payment-options">


<div class="payment-option wave">

<input
type="radio"
id="wave"
name="payment_method"
value="wave"
required
>

<label for="wave">

🔵 Wave

</label>

</div>


<div class="payment-option orange">

<input
type="radio"
id="orange"
name="payment_method"
value="orange"
>

<label for="orange">

🟠 Orange Money

</label>

</div>


<div class="payment-option mtn">

<input
type="radio"
id="mtn"
name="payment_method"
value="mtn"
>

<label for="mtn">

🟡 MTN Mobile Money

</label>

</div>


<div class="payment-option moov">

<input
type="radio"
id="moov"
name="payment_method"
value="moov"
>

<label for="moov">
🟢 Moov Money

</label>

</div>


</div>


<button
type="submit"
class="submit-btn"
>

Continuer vers le paiement

<i class="fa-solid fa-arrow-right"></i>

</button>


</form>


<a
href="cart.php"
class="back"
>

<i class="fa-solid fa-arrow-left"></i>

Retour au panier

</a>


</div>


<!-- =========================
RÉSUMÉ
========================= -->

<div class="summary">


<h2>

<i class="fa-solid fa-receipt"></i>

Votre commande

</h2>


<?php foreach ($cart_items as $item): ?>


<?php

$subtotal =
    $item["price"] *
    $item["quantity"];

?>


<div class="summary-item">


<div class="summary-image">

<img
src="images/<?php echo htmlspecialchars($item["image"]); ?>"
alt="<?php echo htmlspecialchars($item["name"]); ?>"
>

</div>


<div class="summary-info">

<h3>

<?php echo htmlspecialchars($item["name"]); ?>

</h3>

<p>

Quantité :
<?php echo intval($item["quantity"]); ?>

</p>

</div>


<div class="summary-price">

<?php

echo number_format(
    $subtotal,
    0,
    ",",
    " "
);

?>

FCFA

</div>


</div>


<?php endforeach; ?>


<div class="total">

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


</div>


</div>


</div>


</body>

</html>