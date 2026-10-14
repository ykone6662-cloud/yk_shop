<?php

session_start();
include("config.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: cart.php");
    exit();
}

$cart_id = intval($_GET["id"]);
$user_id = intval($_SESSION["user_id"]);

// Supprimer uniquement l'article appartenant à cet utilisateur
$sql = "
    DELETE FROM cart
    WHERE id = $cart_id
    AND user_id = $user_id
";

mysqli_query($conn, $sql);

// Retour au panier
header("Location: cart.php");
exit();

?>