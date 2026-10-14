<?php
session_start();
include "config.php";

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

// Récupérer tous les produits
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>YK Shop</title>

<link rel="stylesheet" href="css/accueil.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include("header.php"); ?>


<section class="hero">

    <h1>
        Bienvenue <?php echo htmlspecialchars($_SESSION["user"]); ?> 👋
    </h1>

    <p>
        Découvrez les meilleurs produits au meilleur prix.
    </p>

</section>


<section class="products">

    <h2>Nos produits</h2>

    <div class="product-grid">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <div class="card">

                    <img
                        src="images/<?php echo htmlspecialchars($row["image"]); ?>"
                        alt="<?php echo htmlspecialchars($row["name"]); ?>"
                    >

                    <h3>
                        <?php echo htmlspecialchars($row["name"]); ?>
                    </h3>

                    <p class="description">
                        <?php echo htmlspecialchars($row["description"]); ?>
                    </p>

                    <p class="category">
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </p>

                    <p class="price">
                        <?php echo number_format($row["price"], 0, ',', ' '); ?> FCFA
                    </p>

                    <a
                        href="add_to_cart.php?id=<?php echo $row["id"]; ?>"
                        class="add-cart"
                    >
                        <i class="fa-solid fa-cart-plus"></i>
                        Ajouter au panier
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="no-product">
                Aucun produit disponible pour le moment.
            </p>

        <?php endif; ?>

    </div>

</section>


</body>
</html>