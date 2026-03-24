<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!defined('QZ_CERTIFICATE_PATH') || !is_file(QZ_CERTIFICATE_PATH) || !is_readable(QZ_CERTIFICATE_PATH)) {
    http_response_code(500);
    echo 'QZ certificate file not found or not readable at path: ' . (defined('QZ_CERTIFICATE_PATH') ? QZ_CERTIFICATE_PATH : '(undefined)');
    exit;
}

$certificate = file_get_contents(QZ_CERTIFICATE_PATH);
if ($certificate === false || trim($certificate) === '') {
    http_response_code(500);
    echo 'QZ certificate file is empty or unreadable.';
    exit;
}

echo $certificate;
