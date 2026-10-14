<?php

/*
|--------------------------------------------------------------------------
| YK SHOP - SERVEUR D'IMAGES PRODUITS
|--------------------------------------------------------------------------
| Cherche automatiquement l'image dans :
|
| /home/www/
| /home/www/images/
| /home/www/images/products/
|
|--------------------------------------------------------------------------
*/

$file = trim((string)($_GET["file"] ?? ""));

if ($file === "") {
    http_response_code(404);
    exit;
}

/*
|--------------------------------------------------------------------------
| Nettoyage
|--------------------------------------------------------------------------
*/

$file = str_replace("\\", "/", $file);
$file = ltrim($file, "/");

/*
|--------------------------------------------------------------------------
| Empêcher les chemins dangereux
|--------------------------------------------------------------------------
*/

$file = str_replace("..", "", $file);

/*
|--------------------------------------------------------------------------
| Si la base contient déjà :
|
| images/photo.jpg
| images/products/photo.jpg
| products/photo.jpg
|
| on récupère uniquement le nom du fichier.
|--------------------------------------------------------------------------
*/

$filename = basename($file);

if ($filename === "" || $filename === "." || $filename === "..") {
    http_response_code(404);
    exit;
}

/*
|--------------------------------------------------------------------------
| Extensions autorisées
|--------------------------------------------------------------------------
*/

$allowedExtensions = [
    "jpg",
    "jpeg",
    "png",
    "webp",
    "gif"
];

$extension = strtolower(
    pathinfo($filename, PATHINFO_EXTENSION)
);

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(404);
    exit;
}

/*
|--------------------------------------------------------------------------
| Emplacements possibles
|--------------------------------------------------------------------------
*/

$possiblePaths = [

    __DIR__ . "/" . $filename,

    __DIR__ . "/images/" . $filename,

    // Le dossier actuellement utilisé sur l'hébergement est au singulier.
    __DIR__ . "/image/" . $filename,

    __DIR__ . "/images/products/" . $filename,

    __DIR__ . "/image/products/" . $filename,

    // Compatibilité avec les images déjà enregistrées par l'ancien formulaire admin.
    __DIR__ . "/Admin/images/products/" . $filename

];

/*
|--------------------------------------------------------------------------
| Chercher le fichier
|--------------------------------------------------------------------------
*/

$foundFile = null;

foreach ($possiblePaths as $path) {

    if (is_file($path)) {

        $foundFile = $path;

        break;
    }
}

/*
|--------------------------------------------------------------------------
| Image introuvable
|--------------------------------------------------------------------------
*/

if ($foundFile === null) {

    http_response_code(404);

    exit;
}

/*
|--------------------------------------------------------------------------
| Type MIME
|--------------------------------------------------------------------------
*/

$mimeTypes = [

    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png",
    "webp" => "image/webp",
    "gif"  => "image/gif"

];

$mime = $mimeTypes[$extension] ?? "application/octet-stream";

/*
|--------------------------------------------------------------------------
| Envoyer l'image
|--------------------------------------------------------------------------
*/

header("Content-Type: " . $mime);

header("Cache-Control: public, max-age=86400");

header(
    "Content-Length: " . filesize($foundFile)
);

readfile($foundFile);

exit;
