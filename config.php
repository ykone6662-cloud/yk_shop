<?php

ob_start();

function ykshop_env($key, $default = null)
{
    $value = getenv($key);

    if ($value === false || $value === null) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }

    if ($value === null || $value === "") {
        return $default;
    }

    return $value;
}

/*
|--------------------------------------------------------------------------
| BASE DE DONNÉES
|--------------------------------------------------------------------------
*/

$db_host = ykshop_env("DB_HOST", "127.0.0.1");
$db_user = ykshop_env("DB_USER", "root");
$db_password = ykshop_env("DB_PASSWORD", "");
$db_name = ykshop_env("DB_NAME", "ykshop");

$conn = new mysqli(
    $db_host,
    $db_user,
    $db_password,
    $db_name
);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données. Vérifiez la configuration DB_HOST, DB_USER, DB_PASSWORD et DB_NAME.");
}

$conn->set_charset("utf8mb4");

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.cookie_httponly", "1");
    ini_set("session.cookie_samesite", "Lax");

    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
        ini_set("session.cookie_secure", "1");
    }

    session_start();
}

/*
|--------------------------------------------------------------------------
| EXPIRATION DE SESSION
|--------------------------------------------------------------------------
*/

$session_timeout = 1800;

if (isset($_SESSION["LAST_ACTIVITY"])) {
    if (time() - $_SESSION["LAST_ACTIVITY"] > $session_timeout) {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: index.php?timeout=1");
        exit();
    }
}

$_SESSION["LAST_ACTIVITY"] = time();