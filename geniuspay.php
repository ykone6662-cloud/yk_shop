```php
<?php
/**
 * YK SHOP - Configuration et fonctions GeniusPay
 */

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

if (!defined('GENIUSPAY_BASE_URL')) {
    define('GENIUSPAY_BASE_URL', ykshop_env('GENIUSPAY_BASE_URL', 'https://geniuspay.ci/api/v1/merchant'));
}

/*
|--------------------------------------------------------------------------
| CLÉS GENIUSPAY
|--------------------------------------------------------------------------
| Stocker dans les variables d'environnement, pas dans le dépôt.
|--------------------------------------------------------------------------
*/

define('GENIUSPAY_API_KEY', ykshop_env('GENIUSPAY_API_KEY', ''));
define('GENIUSPAY_API_SECRET', ykshop_env('GENIUSPAY_API_SECRET', ''));

define('GENIUSPAY_WEBHOOK_SECRET', ykshop_env('GENIUSPAY_WEBHOOK_SECRET', ''));

define('GENIUSPAY_ENVIRONMENT', ykshop_env('GENIUSPAY_ENVIRONMENT', 'sandbox'));

/**
 * Effectuer une requête vers GeniusPay
 */
function geniuspay_request($method, $endpoint, $data = null)
{
    if (GENIUSPAY_API_KEY === '' || GENIUSPAY_API_SECRET === '') {
        return [
            'success' => false,
            'http_code' => 0,
            'error' => [
                'code' => 'MISSING_CREDENTIALS',
                'message' => 'Les identifiants GeniusPay ne sont pas configurés.'
            ]
        ];
    }

    $url = rtrim(GENIUSPAY_BASE_URL, '/') . '/' . ltrim($endpoint, '/');

    $ch = curl_init();

    $headers = [
        'X-API-Key: ' . GENIUSPAY_API_KEY,
        'X-API-Secret: ' . GENIUSPAY_API_SECRET,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $method = strtoupper($method);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);

        if ($data !== null) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }
    }

    $response = curl_exec($ch);

    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false || $curl_error) {
        return [
            'success' => false,
            'http_code' => $http_code,
            'error' => [
                'code' => 'CURL_ERROR',
                'message' => $curl_error ?: 'Erreur de connexion à GeniusPay.'
            ]
        ];
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        return [
            'success' => false,
            'http_code' => $http_code,
            'error' => [
                'code' => 'INVALID_RESPONSE',
                'message' => 'Réponse invalide reçue de GeniusPay.'
            ],
            'raw_response' => $response
        ];
    }

    $decoded['http_code'] = $http_code;

    return $decoded;
}

/**
 * Créer un paiement GeniusPay
 */
function geniuspay_create_payment(
    $amount,
    $order_id,
    $user_id,
    $customer_name,
    $customer_email,
    $customer_phone,
    $success_url,
    $error_url
) {
    $amount = (float)$amount;

    $payload = [
        'amount' => $amount,
        'currency' => 'XOF',

        'description' => 'Commande YK Shop #' . $order_id,

        'customer' => [
            'name' => $customer_name,
            'email' => $customer_email,
            'phone' => $customer_phone,
            'country' => 'CI'
        ],

        'metadata' => [
            'order_id' => (string)$order_id,
            'user_id' => (string)$user_id
        ],

        'success_url' => $success_url,
        'error_url' => $error_url
    ];

    return geniuspay_request(
        'POST',
        '/payments',
        $payload
    );
}

/**
 * Récupérer un paiement par référence
 */
function geniuspay_get_payment($reference)
{
    if (empty($reference)) {
        return [
            'success' => false,
            'error' => [
                'code' => 'EMPTY_REFERENCE',
                'message' => 'Référence de paiement absente.'
            ]
        ];
    }

    return geniuspay_request(
        'GET',
        '/payments/' . rawurlencode($reference)
    );
}

/**
 * Transformer un numéro ivoirien en format international.
 *
 * Exemple :
 * 07 12 34 56 78
 * devient
 * +2250712345678
 */
function geniuspay_normalize_phone($phone)
{
    $phone = trim($phone);

    $phone = preg_replace('/[^\d+]/', '', $phone);

    if (strpos($phone, '+225') === 0) {
        return $phone;
    }

    if (strpos($phone, '225') === 0) {
        return '+' . $phone;
    }

    if (strlen($phone) === 10 && $phone[0] === '0') {
        return '+225' . $phone;
    }

    return $phone;
}