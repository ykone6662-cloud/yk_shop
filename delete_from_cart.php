<?php

session_start();
include("config.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Vérifier qu'un ID de panier est fourni
if (!isset($_GET["id"])) {
    header("Location: cart.php");
    exit();
}

$cart_id = intval($_GET["id"]);
$user_id = intval($_SESSION["user_id"]);

// Supprimer uniquement l'article appartenant à l'utilisateur connecté
$sql = "DELETE FROM cart
        WHERE id = $cart_id
        AND user_id = $user_id";

if (mysqli_query($conn, $sql)) {

    // Retourner au panier
    header("Location: cart.php");
    exit();

} else {

    die("Erreur lors de la suppression : " . mysqli_error($conn));

}

?>