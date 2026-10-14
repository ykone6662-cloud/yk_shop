<?php

header("Content-Type: text/plain; charset=utf-8");

$urls = [
    "https://geniuspay.ci",
    "https://geniuspay.ci/api/v1/merchant",
    "https://pay.genius.ci",
    "https://pay.genius.ci/api/v1/merchant"
];

echo "====================================\n";
echo "TEST CONNEXION GENIUSPAY\n";
echo "====================================\n\n";

echo "PHP : " . PHP_VERSION . "\n";
echo "cURL : " . (function_exists("curl_init") ? "ACTIF" : "INACTIF") . "\n\n";

foreach ($urls as $url) {

    echo "====================================\n";
    echo "TEST : " . $url . "\n";
    echo "====================================\n";

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
    ]);

    $response = curl_exec($ch);

    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $primaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);

    echo "HTTP : " . $httpCode . "\n";
    echo "IP : " . $primaryIp . "\n";
    echo "Erreur cURL : " . $errno . "\n";

    if ($error !== "") {
        echo "Message : " . $error . "\n";
    }

    if ($response !== false) {
        echo "Connexion : OK\n";
    } else {
        echo "Connexion : ECHEC\n";
    }

    echo "\n";
}