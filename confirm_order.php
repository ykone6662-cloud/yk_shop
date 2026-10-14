<?php

session_start();
include("config.php");

// Vérifier la connexion
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Vérifier les données du checkout
if (!isset($_SESSION["checkout"])) {
    header("Location: checkout.php");
    exit();
}

// Vérifier les informations de paiement
if (!isset($_SESSION["payment"])) {
    header("Location: payment.php");
    exit();
}

$user_id = intval($_SESSION["user_id"]);

$checkout = $_SESSION["checkout"];
$payment = $_SESSION["payment"];

$total = floatval($checkout["total"]);
$payment_method = $payment["method"];
$payment_phone = $payment["phone"];

// Vérifier le panier
$cart_query = mysqli_query(
    $conn,
    "SELECT 
        cart.product_id,
        cart.quantity,
        products.price
     FROM cart
     INNER JOIN products
        ON cart.product_id = products.id
     WHERE cart.user_id = '$user_id'"
);

if (!$cart_query || mysqli_num_rows($cart_query) == 0) {
    header("Location: cart.php");
    exit();
}

// Démarrer une transaction
mysqli_begin_transaction($conn);

try {

    /*
     * 1. Créer la commande
     *
     * Pour le moment le statut est "pending".
     * Le paiement réel sera confirmé plus tard par l'API.
     */

    $status = "pending";

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO orders
        (user_id, total, status, created_at)
        VALUES (?, ?, ?, NOW())"
    );

    if (!$stmt) {
        throw new Exception("Erreur lors de la préparation de la commande.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ids",
        $user_id,
        $total,
        $status
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Impossible de créer la commande.");
    }

    $order_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);


    /*
     * 2. Ajouter les produits dans order_items
     */

    $stmt_item = mysqli_prepare(
        $conn,
        "INSERT INTO order_items
        (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );

    if (!$stmt_item) {
        throw new Exception(
            "Erreur lors de la préparation des articles."
        );
    }

    while ($item = mysqli_fetch_assoc($cart_query)) {

        $product_id = intval($item["product_id"]);
        $quantity = intval($item["quantity"]);
        $price = floatval($item["price"]);

        mysqli_stmt_bind_param(
            $stmt_item,
            "iiid",
            $order_id,
            $product_id,
            $quantity,
            $price
        );

        if (!mysqli_stmt_execute($stmt_item)) {
            throw new Exception(
                "Impossible d'ajouter un produit à la commande."
            );
        }
    }

    mysqli_stmt_close($stmt_item);


    /*
     * 3. Vider le panier
     */

    $delete_cart = mysqli_query(
        $conn,
        "DELETE FROM cart
         WHERE user_id = '$user_id'"
    );

    if (!$delete_cart) {
        throw new Exception(
            "Impossible de vider le panier."
        );
    }


    /*
     * 4. Tout s'est bien passé
     */

    mysqli_commit($conn);


    /*
     * Conserver les informations utiles
     * pour la page de confirmation.
     */

    $_SESSION["order_success"] = [
        "order_id" => $order_id,
        "total" => $total,
        "payment_method" => $payment_method,
        "payment_phone" => $payment_phone
    ];


    // Nettoyer les anciennes données
    unset($_SESSION["checkout"]);
    unset($_SESSION["payment"]);


    // Redirection
    header(
        "Location: order_success.php"
    );

    exit();

} catch (Exception $e) {

    // Annuler toutes les modifications
    mysqli_rollback($conn);

    die(
        "Une erreur est survenue : "
        . htmlspecialchars($e->getMessage())
    );
}
?>