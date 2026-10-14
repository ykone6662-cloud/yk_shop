<?php
session_start();
include("config.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

// Récupérer les informations de l'utilisateur
$statement = mysqli_prepare(
    $conn,
    "SELECT id, fullname, email, phone, created_at FROM users WHERE id = ? LIMIT 1"
);

if (!$statement) {
    http_response_code(500);
    exit("Une erreur est survenue lors du chargement du profil.");
}

mysqli_stmt_bind_param($statement, "i", $user_id);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);

if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    exit("Utilisateur introuvable.");
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($statement);

$photo_directory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles";
$photo_extensions = ["jpg", "jpeg", "png", "webp"];
$profile_photo_file = null;

foreach ($photo_extensions as $extension) {
    $candidate = $photo_directory . DIRECTORY_SEPARATOR . "profile-" . $user_id . "." . $extension;

    if (is_file($candidate)) {
        $profile_photo_file = $candidate;
        break;
    }
}

$fullname = trim((string) $user["fullname"]);
$name_parts = preg_split('/\s+/', $fullname, -1, PREG_SPLIT_NO_EMPTY);
$initials = "U";

if (!empty($name_parts)) {
    $initials = mb_strtoupper(mb_substr($name_parts[0], 0, 1));

    if (count($name_parts) > 1) {
        $initials .= mb_strtoupper(mb_substr($name_parts[count($name_parts) - 1], 0, 1));
    }
}

try {
    $date = new DateTime($user["created_at"]);
    $months = [
        1 => "janvier",
        2 => "février",
        3 => "mars",
        4 => "avril",
        5 => "mai",
        6 => "juin",
        7 => "juillet",
        8 => "août",
        9 => "septembre",
        10 => "octobre",
        11 => "novembre",
        12 => "décembre"
    ];
    $member_since = $months[(int) $date->format("n")] . " " . $date->format("Y");
} catch (Exception $exception) {
    $member_since = "Date non disponible";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#087bea">
    <title>Mon profil - YK Shop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root {
            --blue: #087bea;
            --blue-dark: #0758bb;
            --ink: #172235;
            --muted: #718096;
            --page: #f5f8fc;
            --line: #e7edf5;
            --card: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            min-width: 320px;
            margin: 0;
            color: var(--ink);
            background: var(--page);
            font-family: "DM Sans", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .profile-page {
            position: relative;
            isolation: isolate;
            min-height: calc(100vh - 88px);
            overflow: hidden;
            padding: 52px 18px 68px;
        }

        .profile-page::before,
        .profile-page::after {
            position: absolute;
            z-index: -1;
            width: 340px;
            height: 340px;
            content: "";
            border-radius: 50%;
            filter: blur(2px);
            pointer-events: none;
        }

        .profile-page::before {
            top: -170px;
            left: -130px;
            background: radial-gradient(circle, rgba(130, 205, 255, .33), rgba(130, 205, 255, 0) 70%);
        }

        .profile-page::after {
            right: -150px;
            bottom: -210px;
            background: radial-gradient(circle, rgba(72, 141, 242, .16), rgba(72, 141, 242, 0) 70%);
        }

        .profile-shell {
            display: grid;
            grid-template-columns: minmax(275px, .85fr) minmax(0, 1.35fr);
            width: min(100%, 960px);
            margin: 0 auto;
            overflow: hidden;
            background: var(--card);
            border: 1px solid rgba(221, 232, 245, .9);
            border-radius: 22px;
            box-shadow: 0 18px 48px rgba(20, 54, 94, .12);
        }

        .profile-summary {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            padding: 46px 34px 34px;
            color: #fff;
            text-align: center;
            background: linear-gradient(145deg, #0865c9 0%, #087bea 53%, #16a6e9 100%);
        }

        .profile-summary::before,
        .profile-summary::after {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 50%;
            content: "";
        }

        .profile-summary::before {
            top: -115px;
            right: -105px;
            width: 260px;
            height: 260px;
        }

        .profile-summary::after {
            bottom: -125px;
            left: -90px;
            width: 220px;
            height: 220px;
        }

        .avatar {
            position: relative;
            z-index: 1;
            display: grid;
            width: 104px;
            height: 104px;
            margin-bottom: 20px;
            place-items: center;
            color: #0876df;
            background: linear-gradient(145deg, #fff, #dff4ff);
            border: 5px solid rgba(255, 255, 255, .32);
            border-radius: 50%;
            box-shadow: 0 12px 28px rgba(0, 59, 135, .25);
            font-family: "Manrope", sans-serif;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .avatar--photo { padding: 0; overflow: hidden; }

        .avatar--photo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-summary h1 {
            position: relative;
            z-index: 1;
            max-width: 100%;
            margin: 0;
            overflow-wrap: anywhere;
            font-family: "Manrope", sans-serif;
            font-size: clamp(23px, 2.4vw, 29px);
            letter-spacing: -.045em;
        }

        .profile-summary > p {
            position: relative;
            z-index: 1;
            margin: 8px 0 29px;
            color: #e4f5ff;
            font-size: 14px;
            line-height: 1.55;
        }

        .member-badge {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: auto;
            padding: 9px 13px;
            color: #eaf8ff;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-details {
            padding: 44px clamp(28px, 5vw, 58px) 38px;
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 8px;
            color: var(--blue);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .profile-details h2 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 26px;
            letter-spacing: -.045em;
        }

        .intro {
            margin: 9px 0 27px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .info-list {
            display: grid;
            gap: 11px;
            margin: 0;
        }

        .info-item {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            gap: 13px;
            align-items: center;
            min-height: 68px;
            padding: 12px 14px;
            background: #fbfcfe;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .info-icon {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            color: var(--blue);
            background: #eaf5ff;
            border-radius: 10px;
            font-size: 16px;
        }

        .info-copy { min-width: 0; }

        .info-copy dt {
            margin-bottom: 3px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .info-copy dd {
            margin: 0;
            overflow-wrap: anywhere;
            color: var(--ink);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }

        .profile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 25px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 45px;
            padding: 11px 14px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease;
        }

        .button:hover { transform: translateY(-2px); }
        .button:focus-visible { outline: 3px solid rgba(8, 123, 234, .28); outline-offset: 2px; }

        .button-primary {
            color: #fff;
            background: linear-gradient(105deg, #087bea, #0868d3);
            box-shadow: 0 8px 18px rgba(8, 123, 234, .22);
        }

        .button-primary:hover { box-shadow: 0 11px 22px rgba(8, 123, 234, .3); }

        .button-secondary {
            color: #426078;
            background: #fff;
            border-color: #d9e5f1;
        }

        .button-secondary:hover { border-color: #a8d3f9; background: #f4faff; }

        .logout-row {
            margin: 19px 0 0;
            text-align: center;
        }

        .logout-row a {
            color: #8d4b55;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .logout-row a:hover { color: #ca3749; text-decoration: underline; }
        .logout-row i { margin-right: 6px; }

        @media (max-width: 720px) {
            .profile-page { padding: 26px 14px 45px; }

            .profile-shell {
                grid-template-columns: 1fr;
                max-width: 540px;
                border-radius: 18px;
            }

            .profile-summary { min-height: 250px; padding: 34px 26px 27px; }
            .avatar { width: 86px; height: 86px; margin-bottom: 14px; font-size: 27px; }
            .profile-summary > p { margin-bottom: 20px; }
            .member-badge { margin-top: 0; }
            .profile-details { padding: 32px 23px 29px; }
            .profile-details h2 { font-size: 23px; }
        }

        @media (max-width: 390px) {
            .profile-page { padding-right: 10px; padding-left: 10px; }
            .profile-details { padding-right: 17px; padding-left: 17px; }
            .profile-actions { grid-template-columns: 1fr; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>

    <main class="profile-page">
        <section class="profile-shell" aria-labelledby="profile-title">
            <aside class="profile-summary">
                <?php if ($profile_photo_file !== null): ?>
                    <div class="avatar avatar--photo">
                        <img src="profile_photo.php" alt="Photo de profil de <?php echo htmlspecialchars($user["fullname"]); ?>">
                    </div>
                <?php else: ?>
                    <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars($initials); ?></div>
                <?php endif; ?>
                <h1 id="profile-title"><?php echo htmlspecialchars($user["fullname"]); ?></h1>
                <p>Votre espace personnel YK Shop</p>
                <span class="member-badge">
                    <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                    Membre depuis <?php echo htmlspecialchars($member_since); ?>
                </span>
            </aside>

            <div class="profile-details">
                <p class="eyebrow"><i class="fa-solid fa-id-card" aria-hidden="true"></i> Mon compte</p>
                <h2>Informations personnelles</h2>
                <p class="intro">Retrouvez vos coordonnées et gérez-les à tout moment.</p>

                <dl class="info-list">
                    <div class="info-item">
                        <div class="info-icon" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
                        <div class="info-copy">
                            <dt>Nom complet</dt>
                            <dd><?php echo htmlspecialchars($user["fullname"]); ?></dd>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></div>
                        <div class="info-copy">
                            <dt>Adresse e-mail</dt>
                            <dd><?php echo htmlspecialchars($user["email"]); ?></dd>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></div>
                        <div class="info-copy">
                            <dt>Téléphone</dt>
                            <dd><?php echo htmlspecialchars($user["phone"]); ?></dd>
                        </div>
                    </div>
                </dl>

                <div class="profile-actions">
                    <a class="button button-primary" href="edit_profile.php">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        Modifier mon profil
                    </a>
                    <a class="button button-secondary" href="home.php">
                        <i class="fa-solid fa-store" aria-hidden="true"></i>
                        Retour à la boutique
                    </a>
                </div>

                <p class="logout-row">
                    <a href="logout.php"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>Se déconnecter</a>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
