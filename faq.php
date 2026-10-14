<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questions fréquentes | YK Shop</title>
    <link rel="stylesheet" href="informations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body id="top">
    <?php include __DIR__ . "/info_header.php"; ?>

    <section class="info-hero">
        <div class="info-hero-inner">
            <span class="info-icon"><i class="fa-solid fa-circle-question"></i></span>
            <span class="info-kicker">CENTRE D'AIDE</span>
            <h1>Questions fréquentes</h1>
            <p>Les réponses aux questions les plus courantes sur votre compte et vos commandes.</p>
        </div>
    </section>

    <main class="info-main">
        <section class="faq-list" aria-label="Questions fréquentes">
            <details open>
                <summary>Comment suivre ma commande ?</summary>
                <p>Connectez-vous à votre compte puis ouvrez <a href="orders.php">Mes commandes</a>. Vous y trouverez le statut et le détail de chaque commande.</p>
            </details>
            <details>
                <summary>Comment mettre à jour mes informations ?</summary>
                <p>Vous pouvez modifier les informations de votre compte depuis la page <a href="profile.php">Profil</a>. Vérifiez-les avant de confirmer une nouvelle commande.</p>
            </details>
            <details>
                <summary>Que signifie le statut de ma commande ?</summary>
                <p>« En attente » indique que la commande est en cours de traitement. Les autres statuts vous informent du paiement, de l'expédition ou de la livraison selon l'avancement de votre commande.</p>
            </details>
            <details>
                <summary>Comment ajouter ou retirer un produit du panier ?</summary>
                <p>Depuis la boutique, utilisez le bouton « Ajouter au panier ». Vous pouvez ensuite modifier les quantités ou supprimer un produit depuis <a href="cart.php">Mon panier</a>.</p>
            </details>
            <details>
                <summary>Je rencontre un problème avec une commande, que faire ?</summary>
                <p>Conservez votre numéro de commande et consultez d'abord son détail. Si vous avez encore besoin d'aide, rendez-vous sur la page <a href="contact.php">Nous contacter</a>.</p>
            </details>
            <details>
                <summary>Comment laisser un avis ?</summary>
                <p>Une fois connecté, ouvrez la page <a href="avis_clients.php">Avis clients</a> et partagez votre expérience. Votre avis est soumis à validation avant publication.</p>
            </details>
        </section>

        <article class="content-card" style="margin-top:20px">
            <h2>Vous ne trouvez pas votre réponse ?</h2>
            <p>Rassemblez votre numéro de commande et les informations utiles, puis contactez l'équipe YK Shop afin de recevoir une assistance plus rapide.</p>
            <div class="help-actions"><a class="info-button" href="contact.php"><i class="fa-solid fa-headset"></i> Nous contacter</a></div>
        </article>
    </main>

    <?php include __DIR__ . "/info_footer.php"; ?>
</body>
</html>
