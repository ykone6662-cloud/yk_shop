<?php
include("config.php");

if (isset($_SESSION["user_id"])) {
    header("Location: home.php");
    exit();
}

if (empty($_SESSION["login_form_token"])) {
    $_SESSION["login_form_token"] = bin2hex(random_bytes(32));
}

$message = "";
$login = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim((string) ($_POST["login"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");
    $form_token = (string) ($_POST["form_token"] ?? "");

    if (!hash_equals($_SESSION["login_form_token"], $form_token)) {
        $message = "Votre session a expiré. Veuillez réessayer.";
    } elseif ($login === "" || $password === "") {
        $message = "Veuillez renseigner votre e-mail ou téléphone, puis votre mot de passe.";
    } else {
        $statement = mysqli_prepare(
            $conn,
            "SELECT id, fullname, email, password FROM users WHERE email = ? OR phone = ? LIMIT 1"
        );

        if (!$statement) {
            $message = "La connexion est temporairement indisponible. Réessayez dans un instant.";
        } else {
            mysqli_stmt_bind_param($statement, "ss", $login, $login);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $user = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($statement);

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int) $user["id"];
                $_SESSION["user"] = $user["fullname"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["LAST_ACTIVITY"] = time();

                header("Location: home.php");
                exit();
            }

            $message = "Identifiants incorrects. Vérifiez vos informations et réessayez.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#087bea">
    <title>Connexion - YK Shop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
    <main class="auth-page">
        <section class="auth-showcase" aria-label="Présentation de YK Shop">
            <a class="brand" href="index.php" aria-label="YK Shop, accueil">
                <img src="product_image.php?file=logo.png" alt="">
                <span>YK Shop</span>
            </a>

            <div class="showcase-copy">
                <p class="eyebrow"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Votre boutique en ligne</p>
                <h1>Tout ce que vous aimez, <span>à portée de main.</span></h1>
                <p>Connectez-vous pour retrouver vos commandes, votre panier et les informations de votre compte.</p>
            </div>

            <div class="showcase-art" aria-hidden="true">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <div class="bag-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <div class="spark spark-one"><i class="fa-solid fa-sparkles"></i></div>
                <div class="spark spark-two"><i class="fa-solid fa-star"></i></div>
            </div>

            <p class="showcase-note"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i> Un espace personnel simple et sécurisé.</p>
        </section>

        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-card">
                <a class="mobile-brand" href="index.php" aria-label="YK Shop, accueil">
                    <img src="product_image.php?file=logo.png" alt="">
                    <span>YK Shop</span>
                </a>

                <p class="form-kicker">Bon retour parmi nous</p>
                <h2 id="login-title">Connexion</h2>
                <p class="form-intro">Saisissez vos identifiants pour accéder à votre compte.</p>

                <?php if ($message !== ""): ?>
                    <div class="login-message" id="message" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form id="login-form" method="POST">
                    <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION["login_form_token"]); ?>">

                    <div class="field">
                        <label for="login">E-mail ou téléphone</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <input id="login" type="text" name="login" value="<?php echo htmlspecialchars($login); ?>" placeholder="ex. nom@exemple.com" autocomplete="username" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <div class="label-row">
                            <label for="password">Mot de passe</label>
                            <a href="contact.php">Besoin d’aide ?</a>
                        </div>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="password" type="password" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
                            <button class="password-toggle" id="toggle-password" type="button" aria-label="Afficher le mot de passe" aria-pressed="false">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button class="submit-button" type="submit"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Se connecter</button>
                </form>

                <p class="register-link">Nouveau sur YK Shop ? <a href="register.php">Créer un compte</a></p>
            </div>
        </section>
    </main>

    <script>
        const password = document.getElementById("password");
        const togglePassword = document.getElementById("toggle-password");
        const toggleIcon = togglePassword.querySelector("i");

        togglePassword.addEventListener("click", function () {
            const willShowPassword = password.type === "password";
            password.type = willShowPassword ? "text" : "password";
            togglePassword.setAttribute("aria-pressed", String(willShowPassword));
            togglePassword.setAttribute("aria-label", willShowPassword ? "Masquer le mot de passe" : "Afficher le mot de passe");
            toggleIcon.classList.toggle("fa-eye", !willShowPassword);
            toggleIcon.classList.toggle("fa-eye-slash", willShowPassword);
        });
    </script>
</body>
</html>
