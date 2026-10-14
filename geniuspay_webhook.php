```php
<?php

/*
|--------------------------------------------------------------------------
| WEBHOOK GENIUSPAY
|--------------------------------------------------------------------------
|
| Ce fichier est appelé automatiquement par GeniusPay.
|
| URL :
| https://ykshop.atwebpages.com/geniuspay_webhook.php
|
|--------------------------------------------------------------------------
*/

require_once "config.php";
require_once "geniuspay.php";


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LE PAYLOAD BRUT
|--------------------------------------------------------------------------
*/

$rawPayload = file_get_contents("php://input");

if (!$rawPayload) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Payload vide."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LES HEADERS
|--------------------------------------------------------------------------
*/

$headers = [];

if (function_exists("getallheaders")) {
    $headers = getallheaders();
}

function webhook_header($name)
{
    global $headers;

    foreach ($headers as $key => $value) {
        if (strtolower($key) === strtolower($name)) {
            return trim($value);
        }
    }

    $serverKey = "HTTP_" .
        strtoupper(
            str_replace("-", "_", $name)
        );

    return isset($_SERVER[$serverKey])
        ? trim($_SERVER[$serverKey])
        : "";
}

$signature =
    webhook_header("X-Webhook-Signature");

$timestamp =
    webhook_header("X-Webhook-Timestamp");

$eventHeader =
    webhook_header("X-Webhook-Event");

$environmentHeader =
    webhook_header("X-Webhook-Environment");


/*
|--------------------------------------------------------------------------
| VÉRIFIER LES HEADERS
|--------------------------------------------------------------------------
*/

if (
    $signature === ""
    ||
    $timestamp === ""
) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Signature ou timestamp absent."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| PROTECTION CONTRE REPLAY ATTACK
|--------------------------------------------------------------------------
*/

if (!is_numeric($timestamp)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Timestamp invalide."
    ]);

    exit();
}

if (abs(time() - (int)$timestamp) > 300) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Timestamp trop ancien."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| VÉRIFIER LA SIGNATURE HMAC
|--------------------------------------------------------------------------
*/

$signatureData =
    $timestamp . "." . $rawPayload;

$expectedSignature = hash_hmac(
    "sha256",
    $signatureData,
    GENIUSPAY_WEBHOOK_SECRET
);

if (!hash_equals(
    $expectedSignature,
    $signature
)) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Signature invalide."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| DÉCODER LE JSON
|--------------------------------------------------------------------------
*/

$payload = json_decode(
    $rawPayload,
    true
);

if (!is_array($payload)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "JSON invalide."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ENVIRONNEMENT
|--------------------------------------------------------------------------
*/

$payloadEnvironment =
    $payload["environment"]
    ?? "";

if (
    $environmentHeader !== ""
    &&
    $payloadEnvironment !== ""
    &&
    strtolower($environmentHeader)
        !== strtolower($payloadEnvironment)
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Environnement invalide."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ÉVÉNEMENT
|--------------------------------------------------------------------------
*/

$event =
    $payload["event"]
    ?? "";

if (
    $eventHeader !== ""
    &&
    $eventHeader !== $event
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Événement invalide."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| DONNÉES DU PAIEMENT
|--------------------------------------------------------------------------
*/

$data =
    $payload["data"]
    ?? [];

$metadata =
    $data["metadata"]
    ?? [];

$order_id =
    isset($metadata["order_id"])
        ? (int)$metadata["order_id"]
        : 0;

$reference =
    $data["reference"]
    ?? "";

$status =
    $data["status"]
    ?? "";

$amount =
    isset($data["amount"])
        ? (float)$data["amount"]
        : 0;

$payment_method =
    $data["payment_method"]
    ?? $data["provider"]
    ?? "geniuspay";


/*
|--------------------------------------------------------------------------
| VÉRIFIER L'ORDER ID
|--------------------------------------------------------------------------
*/

if ($order_id <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "order_id absent des metadata."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| RÉCUPÉRER LA COMMANDE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        user_id,
        total,
        status,
        payment_reference
    FROM orders
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $order_id
);

$stmt->execute();

$result = $stmt->get_result();

$order = $result->fetch_assoc();

$stmt->close();

if (!$order) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Commande introuvable."
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| VÉRIFIER LE MONTANT
|--------------------------------------------------------------------------
*/

if (
    $event === "payment.success"
    ||
    $status === "completed"
) {

    if (
        abs(
            $amount -
            (float)$order["total"]
        ) > 0.01
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Montant du paiement différent."
        ]);

        exit();
    }
}


/*
|--------------------------------------------------------------------------
| ENREGISTRER LE PAIEMENT
|--------------------------------------------------------------------------
*/

if (
    $event === "payment.success"
    ||
    $status === "completed"
) {

    $newStatus = "paid";

    $stmtUpdate = $conn->prepare("
        UPDATE orders
        SET
            status = ?,
            payment_reference = ?,
            payment_method = ?
        WHERE id = ?
    ");

    $stmtUpdate->bind_param(
        "sssi",
        $newStatus,
        $reference,
        $payment_method,
        $order_id
    );

    $stmtUpdate->execute();

    $stmtUpdate->close();

} elseif (
    in_array(
        $event,
        [
            "payment.failed",
            "payment.cancelled",
            "payment.expired"
        ],
        true
    )
    ||
    in_array(
        $status,
        [
            "failed",
            "cancelled",
            "expired"
        ],
        true
    )
) {

    $newStatus = "failed";

    /*
     * Ne jamais transformer une commande déjà payée
     * en commande échouée.
     */

    $stmtUpdate = $conn->prepare("
        UPDATE orders
        SET
            status = ?,
            payment_reference = ?,
            payment_method = ?
        WHERE id = ?
          AND status <> 'paid'
    ");

    $stmtUpdate->bind_param(
        "sssi",
        $newStatus,
        $reference,
        $payment_method,
        $order_id
    );

    $stmtUpdate->execute();

    $stmtUpdate->close();
}


/*
|--------------------------------------------------------------------------
| RÉPONSE À GENIUSPAY
|--------------------------------------------------------------------------
*/

http_response_code(200);

header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "message" => "Webhook traité.",
    "event" => $event,
    "order_id" => $order_id
]);

exit();