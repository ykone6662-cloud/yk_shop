<?php

session_start();
require_once "config.php";

/* =========================
   SÉCURITÉ
========================= */

if (!isset($_SESSION["user_id"]) || !is_numeric($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

/* =========================
   VÉRIFIER CHECKOUT
========================= */

if (
    !isset($_SESSION["checkout"]) ||
    !is_array($_SESSION["checkout"])
) {
    header("Location: checkout.php");
    exit();
}

$checkout = $_SESSION["checkout"];

if (
    !isset($checkout["user_id"]) ||
    (int)$checkout["user_id"] !== $user_id
) {
    unset($_SESSION["checkout"]);

    header("Location: checkout.php");
    exit();
}

/* =========================
   VÉRIFIER PAIEMENT
========================= */

if (
    !isset($_SESSION["payment"]) ||
    !is_array($_SESSION["payment"])
) {
    header("Location: payment.php");
    exit();
}

$payment = $_SESSION["payment"];

if (
    !isset($payment["user_id"]) ||
    (int)$payment["user_id"] !== $user_id
) {
    unset($_SESSION["payment"]);

    header("Location: payment.php");
    exit();
}

$payment_method = $payment["method"] ?? "";
$payment_phone = trim($payment["phone"] ?? "");

$allowed_methods = [
    "wave",
    "orange",
    "mtn",
    "moov"
];

if (
    !in_array($payment_method, $allowed_methods, true) ||
    $payment_phone === ""
) {
    unset($_SESSION["payment"]);

    header("Location: payment.php");
    exit();
}

/* =========================
   ÉVITER DOUBLE COMMANDE
========================= */

if (
    isset($_SESSION["created_order_id"]) &&
    is_numeric($_SESSION["created_order_id"])
) {
    header(
        "Location: confirmation.php?id=" .
        (int)$_SESSION["created_order_id"]
    );

    exit();
}

/* =========================
   TRANSACTION
========================= */

mysqli_begin_transaction($conn);

try {

    /* =========================
       RÉCUPÉRER PANIER
    ========================= */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            cart.product_id,
            cart.quantity,
            products.name,
            products.price,
            products.image
         FROM cart
         INNER JOIN products
            ON cart.product_id = products.id
         WHERE cart.user_id = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    $total = 0;

    while ($item = mysqli_fetch_assoc($result)) {

        $product_id = (int)$item["product_id"];
        $quantity = (int)$item["quantity"];
        $price = (float)$item["price"];

        if ($quantity <= 0) {
            continue;
        }

        if ($price < 0) {
            throw new Exception(
                "Prix produit invalide."
            );
        }

        $subtotal = $price * $quantity;

        $total += $subtotal;

        $items[] = [

            "product_id" => $product_id,

            "quantity" => $quantity,

            "price" => $price,

            "name" => $item["name"],

            "image" => $item["image"],

            "subtotal" => $subtotal

        ];
    }

    mysqli_stmt_close($stmt);

    if (empty($items)) {
        throw new Exception(
            "Votre panier est vide."
        );
    }

    /* =========================
       CRÉER LA COMMANDE
    ========================= */

    /*
     * Ta table orders actuelle possède :
     * id, user_id, total, status, created_at
     */

    $status = "pending";

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO orders
        (user_id, total, status)
        VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ids",
        $user_id,
        $total,
        $status
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }

    $order_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

    if (!$order_id) {
        throw new Exception(
            "Impossible de créer la commande."
        );
    }

    /* =========================
       CRÉER LES ORDER_ITEMS
    ========================= */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO order_items
        (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );

    foreach ($items as $item) {

        $product_id = $item["product_id"];
        $quantity = $item["quantity"];
        $price = $item["price"];

        mysqli_stmt_bind_param(
            $stmt,
            "iiid",
            $order_id,
            $product_id,
            $quantity,
            $price
        );

        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                mysqli_stmt_error($stmt)
            );
        }
    }

    mysqli_stmt_close($stmt);

    /* =========================
       VIDER LE PANIER
    ========================= */

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM cart
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    /* =========================
       VALIDATION
    ========================= */

    mysqli_commit($conn);

    /*
     * On mémorise uniquement l'ID.
     */

    $_SESSION["created_order_id"] = $order_id;

    $_SESSION["payment"]["status"] = "pending";

    header(
        "Location: confirmation.php?id=" .
        $order_id
    );

    exit();

} catch (Throwable $e) {

    mysqli_rollback($conn);

    /*
     * En production, il vaut mieux enregistrer
     * l'erreur dans un fichier plutôt que l'afficher.
     */

    error_log(
        "YK SHOP create_order.php : " .
        $e->getMessage()
    );

    die(
        "Une erreur est survenue lors de la création de votre commande. " .
        "Veuillez réessayer."
    );
}