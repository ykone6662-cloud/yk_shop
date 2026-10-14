<?php

session_start();
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: home.php");
    exit();
}

$product_id = intval($_GET["id"]);

$sql = "
    SELECT *
    FROM products
    WHERE id = $product_id
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Produit introuvable.");
}

$product = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($product["name"]); ?> - YK Shop
</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


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


.container {

    width: 90%;

    max-width: 1000px;

    margin: 50px auto;
}


.product-box {

    background: white;

    border-radius: 20px;

    padding: 35px;

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 45px;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.10);
}


.product-image {

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f8fafc;

    border-radius: 15px;

    padding: 25px;

    min-height: 400px;
}


.product-image img {

    width: 100%;

    max-width: 430px;

    height: 380px;

    object-fit: contain;

    border-radius: 12px;
}


.product-info {

    display: flex;

    flex-direction: column;

    justify-content: center;
}


.category {

    display: inline-block;

    width: fit-content;

    background: #ede9fe;

    color: #5b21b6;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;

    margin-bottom: 15px;
}


.product-info h1 {

    font-size: 34px;

    margin-bottom: 18px;
}


.description {

    color: #666;

    line-height: 1.7;

    margin-bottom: 25px;
}


.price {

    font-size: 30px;

    font-weight: bold;

    color: #4f46e5;

    margin-bottom: 25px;
}


.add-cart {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 10px;

    width: 100%;

    padding: 16px;

    background: linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color: white;

    border-radius: 10px;

    text-decoration: none;

    font-size: 17px;

    font-weight: bold;

    transition: 0.3s;
}


.add-cart:hover {

    transform: translateY(-3px);

    box-shadow:
        0 10px 20px rgba(79,70,229,0.30);
}


.back {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 25px;

    color: #4f46e5;

    text-decoration: none;

    font-weight: bold;
}


@media (max-width: 750px) {

    header {

        flex-direction: column;

        gap: 15px;
    }

    nav {

        flex-wrap: wrap;

        justify-content: center;
    }

    .product-box {

        grid-template-columns: 1fr;

        padding: 20px;
    }

    .product-image {

        min-height: 300px;
    }

    .product-image img {

        height: 280px;
    }

    .product-info h1 {

        font-size: 27px;
    }

}

</style>

</head>


<body>


<?php include("header.php"); ?>


<div class="container">


<div class="product-box">


<div class="product-image">

<img
src="product_image.php?file=<?php echo rawurlencode($product["image"] ?? ""); ?>"
alt="<?php echo htmlspecialchars($product["name"]); ?>"
>

</div>


<div class="product-info">


<span class="category">

<?php echo htmlspecialchars($product["category"]); ?>

</span>


<h1>

<?php echo htmlspecialchars($product["name"]); ?>

</h1>


<p class="description">

<?php

echo nl2br(
    htmlspecialchars($product["description"])
);

?>

</p>


<div class="price">

<?php

echo number_format(
    $product["price"],
    0,
    ",",
    " "
);

?>

FCFA

</div>


<a
href="add_to_cart.php?id=<?php echo $product["id"]; ?>"
class="add-cart"
>

<i class="fa-solid fa-cart-plus"></i>

Ajouter au panier

</a>


</div>


</div>


<a
href="home.php"
class="back"
>

<i class="fa-solid fa-arrow-left"></i>

Retour aux produits

</a>


</div>


</body>

</html>
