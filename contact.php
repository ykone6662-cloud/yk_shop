<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nous contacter | YK Shop</title>
    <link rel="stylesheet" href="informations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body id="top">
    <?php include __DIR__ . "/info_header.php"; ?>

    <section class="info-hero">
        <div class="info-hero-inner">
            <span class="info-icon"><i class="fa-solid fa-headset"></i></span>
            <span class="info-kicker">SERVICE CLIENT</span>
            <h1>Nous contacter</h1>
            <p>Préparez les bonnes informations pour que l'équipe puisse vous aider efficacement.</p>
        </div>
    </section>

    <main class="info-main">
        <section class="steps" aria-label="Préparer votre demande">
            <article class="step"><span class="step-number"><i class="fa-solid fa-receipt"></i></span><h2>Votre commande</h2><p>Indiquez votre numéro de commande lorsque votre demande concerne un achat.</p></article>
            <article class="step"><span class="step-number"><i class="fa-solid fa-message"></i></span><h2>Votre demande</h2><p>Décrivez clairement la situation et joignez les informations utiles.</p></article>
            <article class="step"><span class="step-number"><i class="fa-solid fa-reply"></i></span><h2>Notre réponse</h2><p>Le service client utilise vos coordonnées de compte pour revenir vers vous.</p></article>
        </section>

        <article class="content-card" style="margin-top:20px">
            <h2>Accès rapide à votre espace</h2>
            <p>La plupart des informations sont disponibles directement dans votre compte : suivi de commande, produits enregistrés et coordonnées personnelles.</p>
            <div class="help-actions">
                <a class="info-button" href="orders.php"><i class="fa-solid fa-box-open"></i> Voir mes commandes</a>
                <a class="info-button info-button-outline" href="profile.php"><i class="fa-solid fa-user"></i> Voir mon profil</a>
            </div>
        </article>

        <article class="content-card">
            <h2>Coordonnées du service client</h2>
   
            <h3>📞 <strong>0702124910</strong></h3>
            <p>🕒 <strong>Horaires :</strong> Lundi au vendredi : 08h30 - 19h45</p>
            <p>✉️ <strong>ykshop199@gmail.com</strong></p>
    
        </article>
    </main>

    <?php include __DIR__ . "/info_footer.php"; ?>
</body>
</html>
