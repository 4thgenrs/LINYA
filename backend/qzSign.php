<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$requestToSign = $_POST['request'] ?? $_GET['request'] ?? '';
if ($requestToSign === '') {
    http_response_code(400);
    echo 'Missing request payload.';
    exit;
}

if (!defined('QZ_PRIVATE_KEY_PATH') || !is_file(QZ_PRIVATE_KEY_PATH) || !is_readable(QZ_PRIVATE_KEY_PATH)) {
    http_response_code(500);
    echo 'QZ private key not found. Upload private-key.pem to the configured secure path.';
    exit;
}

$privateKeyPem = file_get_contents(QZ_PRIVATE_KEY_PATH);
if ($privateKeyPem === false || trim($privateKeyPem) === '') {
    http_response_code(500);
    echo 'QZ private key file is empty or unreadable.';
    exit;
}

$privateKey = openssl_pkey_get_private($privateKeyPem);
if ($privateKey === false) {
    http_response_code(500);
    echo 'Unable to load QZ private key.';
    exit;
}

$algorithmMap = [
    'SHA1' => OPENSSL_ALGO_SHA1,
    'SHA256' => OPENSSL_ALGO_SHA256,
    'SHA512' => OPENSSL_ALGO_SHA512,
];

$algorithmName = strtoupper((string)(defined('QZ_SIGNATURE_ALGORITHM') ? QZ_SIGNATURE_ALGORITHM : 'SHA256'));
$opensslAlgorithm = $algorithmMap[$algorithmName] ?? OPENSSL_ALGO_SHA256;

$signature = null;
$signed = openssl_sign($requestToSign, $signature, $privateKey, $opensslAlgorithm);
openssl_free_key($privateKey);

if (!$signed || $signature === null) {
    http_response_code(500);
    echo 'Unable to sign QZ request.';
    exit;
}

echo base64_encode($signature);