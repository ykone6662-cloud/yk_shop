```php
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";
require_once "geniuspay.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];

$order_id = isset($_GET["order_id"])
    ? (int)$_GET["order_id"]
    : 0;

if ($order_id <= 0) {
    die("Commande invalide.");
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LA COMMANDE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        user_id,
        total,
        status,
        payment_reference,
        payment_method,
        created_at
    FROM orders
    WHERE id = ?
      AND user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $order_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();

$stmt->close();

if (!$order) {
    die("Commande introuvable.");
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LE PAIEMENT GENIUSPAY
|--------------------------------------------------------------------------
*/

$payment = null;

if (!empty($order["payment_reference"])) {

    $payment = geniuspay_get_payment(
        $order["payment_reference"]
    );
}


/*
|--------------------------------------------------------------------------
| VÉRIFICATION DU PAIEMENT
|--------------------------------------------------------------------------
*/

if (
    isset($payment["success"])
    &&
    $payment["success"] === true
    &&
    isset($payment["data"])
) {

    $paymentData = $payment["data"];

    $paymentStatus =
        $paymentData["status"]
        ?? "";

    $paymentAmount =
        isset($paymentData["amount"])
        ? (float)$paymentData["amount"]
        : 0;

    $paymentReference =
        $paymentData["reference"]
        ?? $order["payment_reference"];

    $paymentMethod =
        $paymentData["payment_method"]
        ?? "geniuspay";


    /*
    |--------------------------------------------------------------------------
    | VÉRIFIER LE MONTANT
    |--------------------------------------------------------------------------
    */

    if (
        $paymentStatus === "completed"
        &&
        abs($paymentAmount - (float)$order["total"]) < 0.01
    ) {

        $stmtUpdate = $conn->prepare("
            UPDATE orders
            SET status = 'paid',
                payment_reference = ?,
                payment_method = ?
            WHERE id = ?
              AND user_id = ?
        ");

        $stmtUpdate->bind_param(
            "ssii",
            $paymentReference,
            $paymentMethod,
            $order_id,
            $user_id
        );

        $stmtUpdate->execute();
        $stmtUpdate->close();

        unset($_SESSION["created_order_id"]);
        unset($_SESSION["checkout"]);
        unset($_SESSION["payment"]);

        header(
            "Location: confirmation.php?id=" .
            $order_id
        );

        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | PAIEMENT ÉCHOUÉ / ANNULÉ / EXPIRÉ
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $paymentStatus,
            ["failed", "cancelled", "expired"],
            true
        )
    ) {

        $stmtFailed = $conn->prepare("
            UPDATE orders
            SET status = 'failed'
            WHERE id = ?
              AND user_id = ?
              AND status <> 'paid'
        ");

        $stmtFailed->bind_param(
            "ii",
            $order_id,
            $user_id
        );

        $stmtFailed->execute();
        $stmtFailed->close();

        $message = "Le paiement n'a pas été effectué.";
        $statusText = $paymentStatus;

    } else {

        $message = "Votre paiement est encore en cours de traitement.";
        $statusText = $paymentStatus ?: "pending";
    }

} else {

    $message =
        "Impossible de vérifier immédiatement le paiement. " .
        "Votre commande reste en attente.";

    $statusText = "pending";
}

?>
<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Statut du paiement - YK Shop</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .box {
            width: 92%;
            max-width: 600px;
            background: white;
            padding: 35px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0,0,0,.08);
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        h1 {
            margin: 0 0 15px;
        }

        p {
            color: #687386;
            line-height: 1.6;
        }

        .status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 12px;
            background: #f5f7fb;
            font-weight: bold;
        }

        .buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        a {
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 10px;
            background: #168cff;
            color: white;
        }

        a.secondary {
            background: #edf2f7;
            color: #172033;
        }

    </style>

</head>

<body>

<div class="box">

    <?php if ($statusText === "completed"): ?>

        <div class="icon">✅</div>

        <h1>Paiement réussi</h1>

        <p>
            Votre paiement a été confirmé.
        </p>

    <?php elseif (
        in_array(
            $statusText,
            ["failed", "cancelled", "expired"],
            true
        )
    ): ?>

        <div class="icon">❌</div>

        <h1>Paiement non effectué</h1>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php else: ?>

        <div class="icon">⏳</div>

        <h1>Paiement en attente</h1>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <div class="status">

        Commande #<?= $order_id ?>

        <br>

        Montant :
        <?= number_format(
            (float)$order["total"],
            0,
            ",",
            " "
        ) ?>
        FCFA

    </div>

    <div class="buttons">

        <a href="orders.php">
            Mes commandes
        </a>

        <a
            href="home.php"
            class="secondary"
        >
            Accueil
        </a>

    </div>

</div>

</body>

</html>
```
