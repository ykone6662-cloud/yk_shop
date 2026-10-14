<?php
session_start();
require_once __DIR__ . "/config.php";

if (empty($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$userName = trim((string)($_SESSION["user"] ?? "Client"));
$search = trim((string)($_GET["search"] ?? ""));
$products = [];
$cartCount = 0;

if ($search !== "") {
    $pattern = "%" . $search . "%";
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, description, category, price, image
         FROM products
         WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
         ORDER BY id DESC"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $pattern, $pattern, $pattern);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $result = mysqli_query(
        $conn,
        "SELECT id, name, description, category, price, image FROM products ORDER BY id DESC"
    );
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
}

$cartStmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?");
if ($cartStmt) {
    $userId = (int)$_SESSION["user_id"];
    mysqli_stmt_bind_param($cartStmt, "i", $userId);
    if (mysqli_stmt_execute($cartStmt)) {
        $cartResult = mysqli_stmt_get_result($cartStmt);
        if ($cartResult && ($cart = mysqli_fetch_assoc($cartResult))) {
            $cartCount = (int)$cart["total"];
        }
    }
    mysqli_stmt_close($cartStmt);
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function product_image_url($image): string
{
    $image = trim((string)$image);
    if ($image === "") {
        return "image.png";
    }
    // Le serveur d'images gère les anciens chemins et encode les noms spéciaux.
    return "product_image.php?file=" . rawurlencode($image);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b6ee8">
    <title>Accueil | YK Shop</title>
    <link rel="stylesheet" href="accueil.css">
    <link rel="stylesheet" href="informations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body id="top">
    <?php include __DIR__ . "/header.php"; ?>

    <main>
        <section class="hero">
            <div class="hero-inner">
                <div class="hero-copy">
                    <span class="eyebrow"><i class="fa-solid fa-sparkles"></i> Le plaisir de bien choisir</span>
                    <h1>Bonjour <?= e($userName) ?>,<br><span>bienvenue chez YK Shop.</span></h1>
                    <p>Découvrez une sélection pensée pour vous. Trouvez votre prochain coup de cœur en quelques clics.</p>
                    <a href="#products" class="hero-button"><i class="fa-solid fa-arrow-down"></i> Explorer la boutique</a>
                </div>
                <div class="hero-art" aria-hidden="true">
                    <div class="hero-orbit orbit-one"></div>
                    <div class="hero-orbit orbit-two"></div>
                    <div class="hero-bag"><i class="fa-solid fa-bag-shopping"></i></div>
                    <span class="hero-spark spark-one">✦</span><span class="hero-spark spark-two">✧</span>
                    <span class="hero-note">YK<br><b>SHOP</b></span>
                </div>
            </div>
            <div class="hero-wave" aria-hidden="true"></div>
        </section>

        <section class="shop-section" id="products">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">LA BOUTIQUE</span>
                    <h2>Nos produits <span class="product-count"><?= count($products) ?></span></h2>
                    <p>Des articles sélectionnés avec soin, rien que pour vous.</p>
                </div>
                <a class="cart-summary" href="cart.php">
                    <span class="cart-summary-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                    <span>Mon panier<strong><?= $cartCount ?> article<?= $cartCount > 1 ? "s" : "" ?></strong></span>
                    <i class="fa-solid fa-arrow-right cart-summary-arrow"></i>
                </a>
            </div>

            <form class="search-form" action="home.php#products" method="get" role="search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" name="search" value="<?= e($search) ?>" placeholder="Rechercher un produit, une catégorie…" aria-label="Rechercher dans les produits">
                <?php if ($search !== ""): ?><a class="clear-search" href="home.php#products" aria-label="Effacer la recherche"><i class="fa-solid fa-xmark"></i></a><?php endif; ?>
                <button type="submit">Rechercher</button>
            </form>

            <?php if ($search !== ""): ?>
                <p class="search-result">Résultats pour <strong>« <?= e($search) ?> »</strong></p>
            <?php endif; ?>

            <?php if ($products): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="card">
                            <a class="product-image" href="product_details.php?id=<?= (int)$product["id"] ?>" aria-label="Voir <?= e($product["name"]) ?>">
                                <img src="<?= e(product_image_url($product["image"] ?? "")) ?>" alt="<?= e($product["name"]) ?>" loading="lazy" onerror="this.onerror=null;this.src='image.png';">
                                <?php if (!empty($product["category"])): ?><span class="product-category"><?= e($product["category"]) ?></span><?php endif; ?>
                            </a>
                            <div class="card-content">
                                <h3><a href="product_details.php?id=<?= (int)$product["id"] ?>"><?= e($product["name"]) ?></a></h3>
                                <p class="description"><?= e($product["description"] ?? "") ?></p>
                                <div class="card-bottom">
                                    <p class="price"><?= number_format((float)$product["price"], 0, ",", " ") ?><span> FCFA</span></p>
                                    <a class="details-button" href="product_details.php?id=<?= (int)$product["id"] ?>" aria-label="Détails de <?= e($product["name"]) ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                </div>
                                <a class="cart-button" href="add_to_cart.php?id=<?= (int)$product["id"] ?>"><i class="fa-solid fa-cart-plus"></i> Ajouter au panier</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-products">
                    <span class="empty-icon"><i class="fa-solid fa-box-open"></i></span>
                    <h3><?= $search !== "" ? "Aucun résultat trouvé" : "La boutique se prépare" ?></h3>
                    <p><?= $search !== "" ? "Essayez un autre mot-clé pour trouver votre produit." : "Nos produits seront bientôt disponibles. Revenez nous voir très bientôt !" ?></p>
                    <?php if ($search !== ""): ?><a href="home.php#products" class="empty-link">Voir tous les produits</a><?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="reviews-teaser" aria-labelledby="reviews-title">
            <div>
                <span class="section-kicker">VOTRE EXPÉRIENCE</span>
                <h2 id="reviews-title">Votre avis compte.</h2>
                <p>Partagez votre expérience après votre achat. Chaque avis est lu et publié après vérification.</p>
            </div>
            <a class="reviews-teaser-link" href="avis_clients.php">Voir les avis <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </main>

    <?php include __DIR__ . "/info_footer.php"; ?>
</body>
</html>
