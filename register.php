<?php
include "config.php";

$error = "";
$fullname = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim((string)($_POST["fullname"] ?? ""));
    $email = trim((string)($_POST["email"] ?? ""));
    $phone = trim((string)($_POST["phone"] ?? ""));
    $password = (string)($_POST["password"] ?? "");
    $confirmPassword = (string)($_POST["confirmPassword"] ?? "");

    if ($fullname === "" || $email === "" || $phone === "" || $password === "") {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse e-mail est invalide.";
    } elseif (!preg_match('/^[0-9+()\s.-]{8,20}$/', $phone)) {
        $error = "Le numéro de téléphone est invalide.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");

        if (!$checkStmt) {
            $error = "Une erreur technique est survenue. Réessayez plus tard.";
        } else {
            mysqli_stmt_bind_param($checkStmt, "ss", $email, $phone);
            mysqli_stmt_execute($checkStmt);
            $existing = mysqli_stmt_get_result($checkStmt);
            $alreadyExists = $existing && mysqli_num_rows($existing) > 0;
            mysqli_stmt_close($checkStmt);

            if ($alreadyExists) {
                $error = "Un compte avec cet e-mail ou ce numéro de téléphone existe déjà.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO users (fullname, email, phone, password) VALUES (?, ?, ?, ?)"
                );

                if (!$insertStmt) {
                    $error = "Une erreur technique est survenue. Réessayez plus tard.";
                } else {
                    mysqli_stmt_bind_param($insertStmt, "ssss", $fullname, $email, $phone, $passwordHash);

                    if (mysqli_stmt_execute($insertStmt)) {
                        mysqli_stmt_close($insertStmt);
                        header("Location: index.php?registered=1");
                        exit();
                    }

                    $error = "Impossible de créer le compte pour le moment.";
                    mysqli_stmt_close($insertStmt);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>

    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

    <div class="login-box">

        <div class="logo">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>

        <h1>Créer un compte</h1>

        <p>Inscrivez-vous gratuitement sur YK Shop</p>

        <?php if ($error !== ""): ?>
            <p class="error-message" style="color: #b00020; margin: 12px 0; font-weight: 600;">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form action="" method="POST">

            <!-- Nom complet -->
            <div class="input-box">
                <i class="fa-solid fa-user icon-left"></i>

                <input
                    type="text"
                    name="fullname"
                    value="<?= htmlspecialchars($fullname, ENT_QUOTES, "UTF-8") ?>"
                    placeholder="Nom complet"
                    required>
            </div>

            <!-- Email -->
            <div class="input-box">
                <i class="fa-solid fa-envelope icon-left"></i>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                    placeholder="Adresse email"
                    required>
            </div>

            <!-- Téléphone -->
            <div class="input-box">
                <i class="fa-solid fa-phone icon-left"></i>

                <input
                    type="text"
                    name="phone"
                    value="<?= htmlspecialchars($phone, ENT_QUOTES, "UTF-8") ?>"
                    placeholder="Téléphone"
                    required>
            </div>

            <!-- Mot de passe -->
            <div class="input-box">

                <i class="fa-solid fa-lock icon-left"></i>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Mot de passe"
                    required>

                <i
                    class="fa-solid fa-eye icon-right"
                    id="togglePassword"></i>

            </div>

            <!-- Confirmation -->
            <div class="input-box">

                <i class="fa-solid fa-lock icon-left"></i>

                <input
                    type="password"
                    id="confirmPassword"
                    name="confirmPassword"
                    placeholder="Confirmer le mot de passe"
                    required>

                <i
                    class="fa-solid fa-eye icon-right"
                    id="toggleConfirmPassword"></i>

            </div>

            <button type="submit">
                Créer un compte
            </button>

            <p class="register">
                Déjà un compte ?
                <a href="index.php">Se connecter</a>
            </p>

        </form>

    </div>

    <script src="js/script.js"></script>

</body>

</html>