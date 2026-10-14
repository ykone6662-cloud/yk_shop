```php
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION["checkout"])) {
    header("Location: checkout.php");
    exit();
}

$checkout = $_SESSION["checkout"];

$user_id = (int)$_SESSION["user_id"];

$fullname = $checkout["fullname"] ?? "";
$phone = $checkout["phone"] ?? "";
$address = $checkout["address"] ?? "";
$city = $checkout["city"] ?? "";
$total = (float)($checkout["total"] ?? 0);

if ($total < 200) {
    die("Le montant minimum d'une transaction GeniusPay est de 200 FCFA.");
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement - YK Shop</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eef6ff, #f8fbff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #172033;
        }

        .box {
            width: 92%;
            max-width: 600px;
            background: white;
            border-radius: 22px;
            padding: 35px;
            box-shadow: 0 15px 50px rgba(0,0,0,.10);
            text-align: center;
        }

        .logo {
            font-size: 30px;
            font-weight: 800;
            color: #168cff;
            margin-bottom: 10px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }

        .description {
            color: #6b7280;
            line-height: 1.6;
        }

        .amount {
            margin: 28px 0;
            padding: 22px;
            background: #f3f8ff;
            border-radius: 16px;
        }

        .amount small {
            color: #6b7280;
        }

        .amount strong {
            display: block;
            margin-top: 8px;
            font-size: 32px;
            color: #168cff;
        }

        .methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 20px 0;
        }

        .method {
            padding: 15px;
            border: 1px solid #e0e6ef;
            border-radius: 12px;
            background: #fafcff;
            font-weight: 600;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: #168cff;
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }

        .btn:hover {
            background: #0877df;
        }

        .back {
            display: inline-block;
            margin-top: 18px;
            color: #687386;
            text-decoration: none;
            font-size: 14px;
        }

        .secure {
            margin-top: 18px;
            color: #7b8494;
            font-size: 13px;
        }

        @media (max-width: 500px) {

            .box {
                padding: 25px 18px;
            }

            .methods {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="box">

    <div class="logo">
        YK SHOP
    </div>

    <h1>
        Paiement sécurisé
    </h1>

    <p class="description">
        Votre commande est prête.
        Vous allez être redirigé vers GeniusPay pour effectuer votre paiement.
    </p>

    <div class="amount">

        <small>Montant à payer</small>

        <strong>
            <?= number_format($total, 0, ",", " ") ?> FCFA
        </strong>

    </div>

    <div class="methods">

        <div class="method">🌊 Wave</div>
        <div class="method">🟠 Orange Money</div>
        <div class="method">🟡 MTN Money</div>
        <div class="method">💳 Carte bancaire</div>

    </div>

    <form method="POST" action="create_order.php">

        <button type="submit" class="btn">
            🔒 Payer avec GeniusPay
        </button>

    </form>

    <div class="secure">
        Vous choisirez votre moyen de paiement sur GeniusPay.
    </div>

    <a href="checkout.php" class="back">
        ← Retour au checkout
    </a>

</div>

</body>
</html>
```
