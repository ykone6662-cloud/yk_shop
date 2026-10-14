<?php
session_start();
include("config.php");

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$message = "";
$error = "";
$warning = "";
$photo_directory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles";
$photo_extensions = ["jpg", "jpeg", "png", "webp"];

if (empty($_SESSION["profile_form_token"])) {
    $_SESSION["profile_form_token"] = bin2hex(random_bytes(32));
}

function getProfilePhotoFile($user_id, $photo_directory, $photo_extensions)
{
    foreach ($photo_extensions as $extension) {
        $candidate = $photo_directory . DIRECTORY_SEPARATOR . "profile-" . $user_id . "." . $extension;

        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

// Récupérer les informations actuelles
$statement = mysqli_prepare(
    $conn,
    "SELECT id, fullname, email, phone FROM users WHERE id = ? LIMIT 1"
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
$profile_photo_file = getProfilePhotoFile($user_id, $photo_directory, $photo_extensions);

// TRAITEMENT DU FORMULAIRE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim((string) ($_POST["fullname"] ?? ""));
    $email = trim((string) ($_POST["email"] ?? ""));
    $phone = trim((string) ($_POST["phone"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");
    $form_token = (string) ($_POST["form_token"] ?? "");
    $uploaded_photo = $_FILES["profile_photo"] ?? null;
    $photo_extension = null;

    if (!hash_equals($_SESSION["profile_form_token"], $form_token)) {
        $error = "Votre session a expiré. Veuillez réessayer.";
    } elseif ($fullname === "" || $email === "" || $phone === "") {
        $error = "Veuillez renseigner tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir une adresse e-mail valide.";
    } elseif (is_array($uploaded_photo) && (int) ($uploaded_photo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $upload_error = (int) ($uploaded_photo["error"] ?? UPLOAD_ERR_NO_FILE);

        if ($upload_error !== UPLOAD_ERR_OK) {
            $error = "L'envoi de la photo a échoué. Veuillez réessayer.";
        } elseif ((int) ($uploaded_photo["size"] ?? 0) > 5 * 1024 * 1024) {
            $error = "La photo ne doit pas dépasser 5 Mo.";
        } else {
            $temporary_file = $uploaded_photo["tmp_name"] ?? "";

            if (!is_string($temporary_file) || !is_uploaded_file($temporary_file)) {
                $error = "Le fichier envoyé n'est pas valide.";
            } else {
                $image_info = @getimagesize($temporary_file);
                $allowed_mimes = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];
                $image_mime = is_array($image_info) ? ($image_info["mime"] ?? "") : "";

                if (!isset($allowed_mimes[$image_mime])) {
                    $error = "Choisissez une image au format JPG, PNG ou WebP.";
                } elseif ($image_info[0] > 5000 || $image_info[1] > 5000) {
                    $error = "Les dimensions de l'image sont trop importantes.";
                } else {
                    $photo_extension = $allowed_mimes[$image_mime];
                }
            }
        }
    }

    if ($error === "") {
        // Vérifier si l'e-mail appartient déjà à un autre compte
        $check_statement = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1"
        );

        if (!$check_statement) {
            $error = "Une erreur est survenue lors de la vérification de l'adresse e-mail.";
        } else {
            mysqli_stmt_bind_param($check_statement, "si", $email, $user_id);
            mysqli_stmt_execute($check_statement);
            $check_result = mysqli_stmt_get_result($check_statement);
            $email_in_use = $check_result && mysqli_num_rows($check_result) > 0;
            mysqli_stmt_close($check_statement);

            if ($email_in_use) {
                $error = "Cette adresse e-mail est déjà utilisée.";
            }
        }
    }

    if ($error === "") {
        if ($password !== "") {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_statement = mysqli_prepare(
                $conn,
                "UPDATE users SET fullname = ?, email = ?, phone = ?, password = ? WHERE id = ?"
            );

            if ($update_statement) {
                mysqli_stmt_bind_param(
                    $update_statement,
                    "ssssi",
                    $fullname,
                    $email,
                    $phone,
                    $hashed_password,
                    $user_id
                );
            }
        } else {
            $update_statement = mysqli_prepare(
                $conn,
                "UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?"
            );

            if ($update_statement) {
                mysqli_stmt_bind_param($update_statement, "sssi", $fullname, $email, $phone, $user_id);
            }
        }

        if (!isset($update_statement) || !$update_statement || !mysqli_stmt_execute($update_statement)) {
            $error = "Une erreur est survenue lors de la mise à jour du profil.";
        } else {
            mysqli_stmt_close($update_statement);

            // Mettre à jour la session et les données affichées
            $_SESSION["user"] = $fullname;
            $_SESSION["email"] = $email;
            $user["fullname"] = $fullname;
            $user["email"] = $email;
            $user["phone"] = $phone;

            if ($photo_extension !== null) {
                if (!is_dir($photo_directory) && !mkdir($photo_directory, 0755, true) && !is_dir($photo_directory)) {
                    $warning = "Vos informations ont été mises à jour, mais le dossier des photos est indisponible.";
                } else {
                    $destination = $photo_directory . DIRECTORY_SEPARATOR . "profile-" . $user_id . "." . $photo_extension;
                    $temporary_file = $uploaded_photo["tmp_name"];

                    if (move_uploaded_file($temporary_file, $destination)) {
                        foreach ($photo_extensions as $extension) {
                            $old_file = $photo_directory . DIRECTORY_SEPARATOR . "profile-" . $user_id . "." . $extension;

                            if ($old_file !== $destination && is_file($old_file)) {
                                @unlink($old_file);
                            }
                        }

                        $profile_photo_file = $destination;
                    } else {
                        $warning = "Vos informations ont été mises à jour, mais l'enregistrement de la photo a échoué.";
                    }
                }
            }

            $message = $photo_extension !== null && $warning === ""
                ? "Profil et photo mis à jour avec succès !"
                : "Profil modifié avec succès !";
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
    <title>Modifier mon profil - YK Shop</title>

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

        .editor-page {
            min-height: calc(100vh - 88px);
            padding: 48px 18px 62px;
        }

        .editor-card {
            width: min(100%, 720px);
            margin: 0 auto;
            padding: clamp(25px, 5vw, 44px);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 16px 42px rgba(20, 54, 94, .1);
        }

        .editor-heading { text-align: center; }

        .profile-icon {
            display: grid;
            width: 76px;
            height: 76px;
            margin: 0 auto 17px;
            place-items: center;
            color: var(--blue);
            background: #e9f5ff;
            border-radius: 22px;
            font-size: 29px;
        }

        h1 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: clamp(25px, 4vw, 31px);
            letter-spacing: -.045em;
        }

        .editor-heading > p {
            margin: 8px 0 29px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .notice {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin: 0 0 18px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.45;
        }

        .success { color: #156d47; background: #e8f9f0; border: 1px solid #c9efd9; }
        .error { color: #a53543; background: #fff0f2; border: 1px solid #ffd8de; }
        .warning { color: #885d16; background: #fff8e6; border: 1px solid #f8e1a6; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group { min-width: 0; }
        .form-group--wide { grid-column: 1 / -1; }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #435871;
            font-size: 13px;
            font-weight: 800;
        }

        .form-group input:not([type="file"]) {
            width: 100%;
            min-height: 47px;
            padding: 11px 13px;
            color: var(--ink);
            background: #fbfcfe;
            border: 1px solid #dce6f0;
            border-radius: 10px;
            font: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-group input:not([type="file"]):focus {
            border-color: #79b9f3;
            box-shadow: 0 0 0 3px rgba(8, 123, 234, .11);
        }

        .photo-field {
            display: flex;
            gap: 17px;
            align-items: center;
            padding: 16px;
            background: #f8fbff;
            border: 1px dashed #9dcef7;
            border-radius: 14px;
        }

        .photo-preview {
            display: grid;
            width: 76px;
            height: 76px;
            flex: 0 0 auto;
            place-items: center;
            overflow: hidden;
            color: var(--blue);
            background: #e7f4ff;
            border: 3px solid #fff;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(10, 108, 203, .12);
            font-size: 28px;
        }

        .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .photo-preview .is-hidden { display: none; }
        .photo-input { min-width: 0; }
        .photo-input label { margin-bottom: 4px; }
        .photo-input p { margin: 0 0 9px; color: var(--muted); font-size: 12px; line-height: 1.45; }
        .photo-input input { width: 100%; max-width: 300px; color: #456079; font: inherit; font-size: 12px; }
        .photo-input input::file-selector-button {
            margin-right: 10px;
            padding: 8px 10px;
            color: #086dcf;
            background: #e7f4ff;
            border: 0;
            border-radius: 7px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
            margin-top: 26px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 47px;
            padding: 12px 16px;
            border: 1px solid transparent;
            border-radius: 10px;
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .button:hover { transform: translateY(-2px); }
        .button:focus-visible { outline: 3px solid rgba(8, 123, 234, .28); outline-offset: 2px; }
        .button-primary { flex: 1 1 230px; color: #fff; background: linear-gradient(105deg, #087bea, #0868d3); box-shadow: 0 8px 18px rgba(8, 123, 234, .22); }
        .button-secondary { flex: 1 1 170px; color: #48627b; background: #fff; border-color: #d9e5f1; }

        @media (max-width: 620px) {
            .editor-page { padding: 28px 13px 42px; }
            .editor-card { padding: 25px 19px; border-radius: 16px; }
            .form-grid { grid-template-columns: 1fr; gap: 15px; }
            .form-group--wide { grid-column: auto; }
        }

        @media (max-width: 420px) {
            .photo-field { align-items: flex-start; gap: 12px; padding: 13px; }
            .photo-preview { width: 62px; height: 62px; font-size: 23px; }
            .photo-input input { max-width: 185px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>

    <main class="editor-page">
        <section class="editor-card" aria-labelledby="editor-title">
            <div class="editor-heading">
                <div class="profile-icon" aria-hidden="true"><i class="fa-solid fa-user-pen"></i></div>
                <h1 id="editor-title">Modifier mon profil</h1>
                <p>Gardez vos informations et votre photo de profil à jour.</p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="notice success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($warning !== ""): ?>
                <div class="notice warning" role="status"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><?php echo htmlspecialchars($warning); ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="notice error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION["profile_form_token"]); ?>">

                <div class="form-grid">
                    <div class="form-group form-group--wide">
                        <div class="photo-field">
                            <div class="photo-preview" aria-hidden="true">
                                <img id="photo-preview"<?php if ($profile_photo_file !== null): ?> src="profile_photo.php"<?php endif; ?> alt=""<?php echo $profile_photo_file === null ? " class=\"is-hidden\"" : ""; ?>>
                                <i id="photo-placeholder" class="fa-solid fa-user<?php echo $profile_photo_file !== null ? " is-hidden" : ""; ?>"></i>
                            </div>
                            <div class="photo-input">
                                <label for="profile_photo">Photo de profil</label>
                                <p>JPG, PNG ou WebP — 5 Mo maximum. Une nouvelle photo remplacera l’ancienne.</p>
                                <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-group--wide">
                        <label for="fullname">Nom complet</label>
                        <input id="fullname" type="text" name="fullname" value="<?php echo htmlspecialchars($user["fullname"]); ?>" autocomplete="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>" autocomplete="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($user["phone"]); ?>" autocomplete="tel" required>
                    </div>

                    <div class="form-group form-group--wide">
                        <label for="password">Nouveau mot de passe <span>(facultatif)</span></label>
                        <input id="password" type="password" name="password" placeholder="Laisser vide pour conserver l'ancien" autocomplete="new-password">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button button-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer les modifications</button>
                    <a class="button button-secondary" href="profile.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour au profil</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        const photoInput = document.getElementById("profile_photo");
        const photoPreview = document.getElementById("photo-preview");
        const photoPlaceholder = document.getElementById("photo-placeholder");

        photoInput.addEventListener("change", function () {
            const photo = this.files && this.files[0];

            if (!photo || !photo.type.startsWith("image/")) {
                return;
            }

            photoPreview.src = URL.createObjectURL(photo);
            photoPreview.classList.remove("is-hidden");
            photoPlaceholder.classList.add("is-hidden");
        });
    </script>
</body>
</html>
