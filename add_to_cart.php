<?php

session_start();
include("config.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

// Vérifier l'ID du produit
if (!isset($_GET["id"])) {
    header("Location: home.php");
    exit();
}

$product_id = intval($_GET["id"]);

// Vérifier que le produit existe
$sql = "SELECT id, name FROM products WHERE id = $product_id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: home.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

$product_name = $product["name"];

// Vérifier si le produit existe déjà dans le panier
$check_sql = "
    SELECT id, quantity
    FROM cart
    WHERE user_id = $user_id
    AND product_id = $product_id
";

$check = mysqli_query($conn, $check_sql);

if (!$check) {
    die("Erreur SQL : " . mysqli_error($conn));
}


// Produit déjà présent
if (mysqli_num_rows($check) > 0) {

    $update_sql = "
        UPDATE cart
        SET quantity = quantity + 1
        WHERE user_id = $user_id
        AND product_id = $product_id
    ";

    mysqli_query($conn, $update_sql);

} else {

    // Nouveau produit
    $insert_sql = "
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES ($user_id, $product_id, 1)
    ";

    mysqli_query($conn, $insert_sql);
}


// Créer le message
$_SESSION["cart_message"] =
    "🛒 " . $product_name . " ajouté au panier !";


// Retourner à la page précédente
if (isset($_SERVER["HTTP_REFERER"])) {

    header("Location: " . $_SERVER["HTTP_REFERER"]);

} else {

    header("Location: home.php");
}

exit();

?>