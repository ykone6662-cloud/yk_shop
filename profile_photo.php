<?php
// Les photos sont servies uniquement au propriétaire du compte connecté.
session_start();

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$photo_directory = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles";
$mime_types = [
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png" => "image/png",
    "webp" => "image/webp"
];
$photo_file = null;
$mime_type = null;

foreach ($mime_types as $extension => $type) {
    $candidate = $photo_directory . DIRECTORY_SEPARATOR . "profile-" . $user_id . "." . $extension;

    if (is_file($candidate)) {
        $photo_file = $candidate;
        $mime_type = $type;
        break;
    }
}

if ($photo_file === null || $mime_type === null) {
    http_response_code(404);
    exit;
}

$size = filesize($photo_file);

if ($size === false) {
    http_response_code(404);
    exit;
}

header("Content-Type: " . $mime_type);
header("Content-Length: " . $size);
header("Cache-Control: private, no-store, max-age=0");
header("X-Content-Type-Options: nosniff");

readfile($photo_file);
exit;
