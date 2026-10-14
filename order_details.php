<?php

session_start();
include("config.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Vérifier l'ID de la commande
if (!isset($_GET["id"])) {
    header("Location: orders.php");
    exit();
}

$order_id = intval($_GET["id"]);
$user_id = intval($_SESSION["user_id"]);

// Récupérer la commande
$order_sql = "
    SELECT *
    FROM orders
    WHERE id = $order_id
    AND user_id = $user_id
";

$order_result = mysqli_query($conn, $order_sql);

if (!$order_result || mysqli_num_rows($order_result) == 0) {
    die("Commande introuvable.");
}

$order = mysqli_fetch_assoc($order_result);


// Récupérer les produits de la commande
$items_sql = "
    SELECT
        order_items.quantity,
        order_items.price,
        products.name,
        products.image
    FROM order_items
    INNER JOIN products
        ON order_items.product_id = products.id
    WHERE order_items.order_id = $order_id
";

$items_result = mysqli_query($conn, $items_sql);

if (!$items_result) {
    die("Erreur SQL : " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Détails de la commande - YK Shop</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    color: #222;
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
}

.logo {
    font-size: 24px;
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
    text-decoration: underline;
}


/* CONTENU */

.container {

    width: 90%;

    max-width: 900px;

    margin: 45px auto;
}

h1 {

    text-align: center;

    margin-bottom: 30px;
}


/* INFORMATIONS COMMANDE */

.order-info {

    background: white;

    padding: 25px;

    border-radius: 15px;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.10);

    margin-bottom: 25px;
}

.info-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 12px 0;

    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.label {
    color: #666;

    font-weight: bold;
}

.value {
    font-weight: bold;
}


/* STATUT */

.status {

    display: inline-block;

    padding: 7px 14px;

    border-radius: 20px;

    background: #fef3c7;

    color: #92400e;

    font-size: 14px;
}


/* PRODUITS */

.products {

    background: white;

    padding: 25px;

    border-radius: 15px;

    box-shadow:
        0 6px 20px rgba(0,0,0,0.10);
}

.products h2 {

    margin-bottom: 20px;
}


/* ARTICLE */

.product {

    display: flex;

    align-items: center;

    gap: 20px;

    padding: 18px 0;

    border-bottom: 1px solid #eee;
}

.product:last-child {
    border-bottom: none;
}

.product img {

    width: 90px;

    height: 80px;

    object-fit: cover;

    border-radius: 10px;
}

.product-info {

    flex: 1;
}

.product-info h3 {

    margin-bottom: 8px;
}

.product-info p {

    color: #666;

    margin-top: 4px;
}

.subtotal {

    font-weight: bold;

    color: #4f46e5;

    font-size: 17px;
}


/* TOTAL */

.total {

    margin-top: 25px;

    padding-top: 20px;

    border-top: 2px solid #eee;

    text-align: right;

    font-size: 24px;

    font-weight: bold;

    color: #4f46e5;
}


/* RETOUR */

.back {

    display: inline-block;

    margin-top: 25px;

    padding: 13px 20px;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    border-radius: 9px;

    font-weight: bold;

    transition: 0.3s;
}

.back:hover {

    background: #372fc4;

    transform: translateY(-2px);
}


/* MOBILE */

@media (max-width: 650px) {

    header {

        flex-direction: column;

        gap: 15px;
    }

    nav {

        flex-wrap: wrap;

        justify-content: center;
    }

    .info-row {

        flex-direction: column;

        align-items: flex-start;

        gap: 6px;
    }

    .product {

        flex-direction: column;

        align-items: flex-start;
    }

    .subtotal {

        align-self: flex-end;
    }

}

</style>

</head>


<body>


<?php include("header.php"); ?>


<div class="container">


<h1>
 Détails de la commande
</h1>


<!-- INFORMATIONS DE LA COMMANDE -->

<div class="order-info">

<div class="info-row">

<span class="label">
Numéro de commande
</span>

<span class="value">
N°<?php echo $order["id"]; ?>
</span>

</div>


<div class="info-row">

<span class="label">
Date
</span>

<span class="value">
<?php echo $order["created_at"]; ?>
</span>

</div>


<div class="info-row">

<span class="label">
Statut
</span>

<span class="status">
<?php echo htmlspecialchars($order["status"]); ?>
</span>

</div>

</div>


<!-- PRODUITS -->

<div class="products">

<h2>
🛍️ Produits commandés
</h2>


<?php if (mysqli_num_rows($items_result) > 0): ?>


<?php while ($item = mysqli_fetch_assoc($items_result)): ?>

<?php

$subtotal =
    $item["price"] *
    $item["quantity"];

?>


<div class="product">


<img
src="images/<?php echo htmlspecialchars($item["image"]); ?>"
alt="<?php echo htmlspecialchars($item["name"]); ?>"
>


<div class="product-info">

<h3>
<?php echo htmlspecialchars($item["name"]); ?>
</h3>

<p>
Prix unitaire :
<?php
echo number_format(
    $item["price"],
    0,
    ",",
    " "
);
?>
 FCFA
</p>

<p>
Quantité :
<?php echo $item["quantity"]; ?>
</p>

</div>


<div class="subtotal">

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


<?php endwhile; ?>


<div class="total">

Total :

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


<?php else: ?>

<p>
Aucun produit trouvé dans cette commande.
</p>

<?php endif; ?>


</div>


<a
href="orders.php"
class="back"
>
← Retour à mes commandes
</a>


</div>


</body>

</html>