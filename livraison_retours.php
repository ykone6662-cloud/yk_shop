<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison et retours | YK Shop</title>
    <link rel="stylesheet" href="informations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body id="top">
    <?php include __DIR__ . "/info_header.php"; ?>

    <section class="info-hero">
        <div class="info-hero-inner">
            <span class="info-icon"><i class="fa-solid fa-truck-fast"></i></span>
            <span class="info-kicker">SUIVI DE COMMANDE</span>
            <h1>Livraison et retours</h1>
            <p>Les repères utiles pour recevoir votre commande et demander de l'aide si nécessaire.</p>
        </div>
    </section>

    <main class="info-main">
        <section class="steps" aria-label="Étapes de votre commande">
            <article class="step"><span class="step-number">01</span><h2>Vous commandez</h2><p>Vérifiez vos informations avant de confirmer le paiement.</p></article>
            <article class="step"><span class="step-number">02</span><h2>Nous préparons</h2><p>Le statut de votre commande est visible dans votre espace personnel.</p></article>
            <article class="step"><span class="step-number">03</span><h2>Vous recevez</h2><p>Conservez les informations de commande jusqu'à la bonne réception.</p></article>
        </section>

        <article class="content-card" style="margin-top:20px">
            <h2>Livraison</h2>
            <p>Les modalités, frais et délais de livraison sont précisés au moment de la commande selon la destination et les produits concernés. Une fois connecté, vous pouvez consulter l'avancement de vos commandes depuis <a href="orders.php">Mes commandes</a>.</p>
            <h3>Avant la réception</h3>
            <p>Vérifiez que l'adresse et le numéro de téléphone saisis lors de la commande sont exacts. Si une information doit être modifiée, contactez le service client dès que possible.</p>
        </article>

        <article class="content-card">
            <h2>Retour ou problème avec un article</h2>
            <p>Si votre article est endommagé, incomplet ou ne correspond pas à la commande, conservez le produit, son emballage et toute preuve utile. Consultez votre commande puis contactez le service client en indiquant votre numéro de commande et une description du problème.</p>
            <h3>Conditions applicables</h3>
            <p>Les conditions d'éligibilité, les délais et les frais éventuels de retour dépendent de la réglementation applicable et de la politique commerciale de YK Shop. Ces informations doivent être confirmées par le responsable de la boutique avant publication.</p>
        </article>

        <div class="help-actions">
            <a class="info-button" href="orders.php"><i class="fa-solid fa-box-open"></i> Mes commandes</a>
            <a class="info-button info-button-outline" href="contact.php"><i class="fa-solid fa-headset"></i> Obtenir de l'aide</a>
        </div>
    </main>

    <?php include __DIR__ . "/info_footer.php"; ?>
</body>
</html>
