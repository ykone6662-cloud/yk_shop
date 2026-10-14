<?php
require_once __DIR__ . "/config.php";

$reviewsReady = mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS customer_reviews (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        rating TINYINT UNSIGNED NOT NULL,
        comment TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY customer_reviews_user_unique (user_id),
        KEY customer_reviews_status_date (status, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
) !== false;

if (empty($_SESSION["review_csrf_token"])) {
    $_SESSION["review_csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_SESSION["user_id"])) {
        $message = "Connectez-vous à votre compte pour laisser un avis.";
        $messageType = "error";
    } elseif (!$reviewsReady) {
        $message = "Le service d'avis est temporairement indisponible. Réessayez plus tard.";
        $messageType = "error";
    } elseif (!hash_equals($_SESSION["review_csrf_token"], (string)($_POST["csrf_token"] ?? ""))) {
        $message = "Votre session a expiré. Actualisez la page avant de réessayer.";
        $messageType = "error";
    } else {
        $rating = (int)($_POST["rating"] ?? 0);
        $comment = trim((string)($_POST["comment"] ?? ""));

        if ($rating < 1 || $rating > 5) {
            $message = "Choisissez une note entre 1 et 5 étoiles.";
            $messageType = "error";
        } elseif ($comment === "" || strlen($comment) < 10 || strlen($comment) > 1500) {
            $message = "Votre avis doit contenir entre 10 et 1 500 caractères.";
            $messageType = "error";
        } else {
            $userId = (int)$_SESSION["user_id"];
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO customer_reviews (user_id, rating, comment, status)
                 VALUES (?, ?, ?, 'pending')
                 ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    comment = VALUES(comment),
                    status = 'pending',
                    updated_at = CURRENT_TIMESTAMP"
            );

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iis", $userId, $rating, $comment);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Merci ! Votre avis a été envoyé et sera publié après validation.";
                    $messageType = "success";
                } else {
                    $message = "Votre avis n'a pas pu être enregistré. Réessayez dans un instant.";
                    $messageType = "error";
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "Votre avis n'a pas pu être enregistré. Réessayez dans un instant.";
                $messageType = "error";
            }
        }
    }
}

$reviews = [];
$averageRating = 0;
if ($reviewsReady) {
    $result = mysqli_query(
        $conn,
        "SELECT r.rating, r.comment, r.created_at, u.fullname
         FROM customer_reviews r
         LEFT JOIN users u ON u.id = r.user_id
         WHERE r.status = 'published'
         ORDER BY r.created_at DESC"
    );
    if ($result) {
        $total = 0;
        while ($review = mysqli_fetch_assoc($result)) {
            $reviews[] = $review;
            $total += (int)$review["rating"];
        }
        if ($reviews) {
            $averageRating = $total / count($reviews);
        }
    }
}

function review_e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function review_name($fullname): string
{
    $parts = preg_split('/\s+/', trim((string)$fullname));
    return !empty($parts[0]) ? $parts[0] : "Client YK Shop";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis clients | YK Shop</title>
    <link rel="stylesheet" href="informations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body id="top">
    <?php include __DIR__ . "/info_header.php"; ?>

    <section class="info-hero">
        <div class="info-hero-inner">
            <span class="info-icon"><i class="fa-solid fa-star"></i></span>
            <span class="info-kicker">EXPÉRIENCES CLIENTS</span>
            <h1>Les avis de nos clients</h1>
            <p>Des retours soumis par les clients de YK Shop et publiés après validation.</p>
        </div>
    </section>

    <main class="info-main">
        <section class="review-intro">
            <div class="review-score">
                <strong><?= $reviews ? number_format($averageRating, 1, ",", " ") : "—" ?></strong>
                <div class="stars" aria-label="Note moyenne sur cinq">★★★★★</div>
                <span><?= count($reviews) ?> avis publié<?= count($reviews) > 1 ? "s" : "" ?></span>
            </div>
            <div class="review-policy">
                <h2>Des retours authentiques</h2>
                <p>Chaque client connecté peut soumettre un avis. Les messages sont vérifiés avant publication pour conserver un espace respectueux et utile à tous.</p>
                <div class="help-actions"><a href="#leave-review" class="info-button">Laisser un avis <i class="fa-solid fa-arrow-down"></i></a></div>
            </div>
        </section>

        <?php if ($reviews): ?>
            <section class="review-list" aria-label="Avis publiés">
                <?php foreach ($reviews as $review): ?>
                    <article class="review-card">
                        <div class="review-meta">
                            <div class="review-author"><span class="review-avatar"><?= review_e(strtoupper(substr(review_name($review["fullname"]), 0, 1))) ?></span><?= review_e(review_name($review["fullname"])) ?></div>
                            <time class="review-date" datetime="<?= review_e(date("Y-m-d", strtotime($review["created_at"]))) ?>"><?= review_e(date("d/m/Y", strtotime($review["created_at"]))) ?></time>
                        </div>
                        <div class="stars" aria-label="<?= (int)$review["rating"] ?> étoiles sur 5"><?= str_repeat("★", (int)$review["rating"]) ?><span style="color:#d7e0e9"><?= str_repeat("★", 5 - (int)$review["rating"]) ?></span></div>
                        <p><?= nl2br(review_e($review["comment"])) ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="empty-reviews">
                <i class="fa-regular fa-comment-dots"></i>
                <h2>Les premiers avis arrivent bientôt</h2>
                <p>Les avis approuvés par YK Shop apparaîtront ici.</p>
            </section>
        <?php endif; ?>

        <section class="review-form" id="leave-review">
            <h2>Partagez votre expérience</h2>
            <p>Votre prénom et votre note pourront être affichés avec votre commentaire après validation.</p>

            <?php if ($message !== ""): ?><p class="form-message form-<?= review_e($messageType) ?>"><?= review_e($message) ?></p><?php endif; ?>

            <?php if (!empty($_SESSION["user_id"])): ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= review_e($_SESSION["review_csrf_token"]) ?>">
                    <div class="form-group">
                        <label>Votre note</label>
                        <div class="rating-input">
                            <input id="star5" name="rating" type="radio" value="5" required><label for="star5" title="5 étoiles">★</label>
                            <input id="star4" name="rating" type="radio" value="4"><label for="star4" title="4 étoiles">★</label>
                            <input id="star3" name="rating" type="radio" value="3"><label for="star3" title="3 étoiles">★</label>
                            <input id="star2" name="rating" type="radio" value="2"><label for="star2" title="2 étoiles">★</label>
                            <input id="star1" name="rating" type="radio" value="1"><label for="star1" title="1 étoile">★</label>
                        </div>
                    </div>
                    <div class="form-group"><label for="comment">Votre avis</label><textarea id="comment" name="comment" minlength="10" maxlength="1500" required placeholder="Racontez votre expérience avec YK Shop..."></textarea></div>
                    <button class="info-button" type="submit"><i class="fa-solid fa-paper-plane"></i> Envoyer mon avis</button>
                </form>
            <?php else: ?>
                <p>Pour publier votre expérience, <a href="index.php">connectez-vous à votre compte</a>.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . "/info_footer.php"; ?>
</body>
</html>
