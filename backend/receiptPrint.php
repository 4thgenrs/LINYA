<?php
// Generates a simple receipt PDF using FPDF and a QR image (Google Chart / QRServer fallback).
// QR content points to a public queue details page for the issued receipt.
// Requires FPDF library available via include path. If you don't have it, install: composer require setasign/fpdf
// or place fpdf.php in vendor or include folder and update the require path below.

// try common FPDF locations (prefer backend/fpdf.php since fpdf.php lives in backend/)
$fpdf_paths = [
    __DIR__ . '/fpdf.php',                      // c:\xampp\htdocs\LINYA\backend\fpdf.php
    __DIR__ . '/fpdf/fpdf.php',                 // c:\xampp\htdocs\LINYA\backend\fpdf/fpdf.php
    __DIR__ . '/../../vendor/fpdf/fpdf.php',    // composer-installed path
    __DIR__ . '/../lib/fpdf.php',               // legacy lib path
];

$fpdf_loaded = false;
foreach ($fpdf_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        if (class_exists('FPDF')) {
            $fpdf_loaded = true;
            break;
        }
    }
}

if (!$fpdf_loaded) {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    die("FPDF library not found. Tried:\n" . implode("\n", $fpdf_paths) . "\n\nPlace fpdf.php in the backend folder or adjust the path in receiptPrint.php.");
}

include '../linya_db/config.php';
require_once __DIR__ . '/../config.php';
session_start();

// Determine whether current viewer is a face-logged session
$face_logged = isset($_SESSION['client']) && is_array($_SESSION['client']);

$queueId = isset($_GET['queue_id']) ? intval($_GET['queue_id']) : 0;
if (!$queueId) { die('Missing queue_id'); }

$q = $conn->prepare("SELECT q.queue_id, q.queue_code, q.office_id, q.created_at, q.status, q.customer_name, q.customer_phone, q.customer_email, o.office_description
                     FROM queues q JOIN offices o ON q.office_id = o.office_id
                     WHERE q.queue_id = ?");
$q->bind_param('i', $queueId);
$q->execute();
$res = $q->get_result();
$queue = $res->fetch_assoc();
$q->close();

if (!$queue) { die('Queue not found'); }

$services = [];
$qs = $conn->prepare("SELECT qs.service_id, s.service_description, qs.soft_copy
                      FROM queue_services qs
                      JOIN services s ON qs.service_id = s.service_id
                      WHERE qs.queue_id = ?");
$qs->bind_param('i', $queueId);
$qs->execute();
$r2 = $qs->get_result();
while ($row = $r2->fetch_assoc()) {
    $services[] = $row;
}
$qs->close();

function build_queue_details_url(int $queueId): string {
    if (defined('PUBLIC_APP_URL') && trim((string)PUBLIC_APP_URL) !== '') {
        return rtrim((string)PUBLIC_APP_URL, '/') . '/queueDetails.php?queue_id=' . rawurlencode((string)$queueId);
    }

    $httpsOn = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    $isHttps = $httpsOn || (isset($_SERVER['SERVER_PORT']) && intval($_SERVER['SERVER_PORT']) === 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? trim((string)$_SERVER['HTTP_HOST']) : '';
    $scriptDir = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '';
    $scriptDir = rtrim($scriptDir, '/');
    $publicRoot = preg_replace('#/backend$#', '', $scriptDir);
    $path = ($publicRoot !== '' ? $publicRoot : '') . '/queueDetails.php?queue_id=' . rawurlencode((string)$queueId);

    if ($host === '') {
        return $path;
    }

    return $scheme . '://' . $host . $path;
}

/**
 * Download remote image to a temporary PNG file.
 * Returns filepath or false.
 */
function download_qr_to_temp(string $url) {
    $tmp = tempnam(sys_get_temp_dir(), 'qr_');
    if ($tmp === false) return false;
    $tmpPng = $tmp . '.png';

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 8,
            'header'  => "User-Agent: LINYA-Receipt/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $img = @file_get_contents($url, false, $ctx);

    // Guard against empty response / HTML error page
    if ($img === false || strlen($img) < 100) {
        @unlink($tmp);
        return false;
    }

    file_put_contents($tmpPng, $img);
    @unlink($tmp);
    return $tmpPng;
}

// --------------------------------------------------
// QR points to the receipt queue details page.
// --------------------------------------------------
$tmp = false;

$detailsUrl = build_queue_details_url((int)$queue['queue_id']);

// Primary QR provider (Google Chart)
$googleQr = "https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=" . urlencode($detailsUrl) . "&chld=L|1";
$tmp = download_qr_to_temp($googleQr);

// Fallback provider (qrserver)
if (!$tmp) {
    $qrServer = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($detailsUrl);
    $tmp = download_qr_to_temp($qrServer);
}

// create PDF receipt (80mm width)
$pdf = new FPDF('P', 'mm', array(80, 200));
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetMargins(6, 6, 6);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 6, 'LINYA', 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, $queue['office_description'], 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, $queue['queue_code'], 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, 'Date:', 0, 0);
$pdf->Cell(0, 6, date('Y-m-d H:i', strtotime($queue['created_at'])), 0, 1);
$pdf->Ln(2);

// print visitor info if present
$pdf->SetFont('Arial', '', 9);
if (!empty($queue['customer_name']) && trim($queue['customer_name']) !== '') {
    $pdf->Cell(30, 6, 'Name:', 0, 0);
    $pdf->Cell(0, 6, $queue['customer_name'], 0, 1);
}
if (!empty($queue['customer_phone']) && trim($queue['customer_phone']) !== '') {
    $pdf->Cell(30, 6, 'Phone:', 0, 0);
    $pdf->Cell(0, 6, $queue['customer_phone'], 0, 1);
}
if (!empty($queue['customer_email']) && trim($queue['customer_email']) !== '') {
    $pdf->Cell(30, 6, 'Email:', 0, 0);
    $pdf->Cell(0, 6, $queue['customer_email'], 0, 1);
}
if (
    (!empty($queue['customer_name']) && trim($queue['customer_name']) !== '') ||
    (!empty($queue['customer_phone']) && trim($queue['customer_phone']) !== '') ||
    (!empty($queue['customer_email']) && trim($queue['customer_email']) !== '')
) {
    $pdf->Ln(2);
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Services:', 0, 1);
$pdf->SetFont('Arial', '', 9);
foreach ($services as $s) {
    $label = $s['service_description'] . (isset($s['soft_copy']) && $s['soft_copy'] ? ' (PDF)' : '');
    $pdf->MultiCell(0, 5, $label, 0, 'L');
}
$pdf->Ln(6);

// Embed QR code when it was generated successfully.
if ($tmp && file_exists($tmp)) {
    $imgWidth = 40; // mm
    $x = ($pdf->GetPageWidth() - $imgWidth) / 2;
    $pdf->Image($tmp, $x, $pdf->GetY(), $imgWidth, $imgWidth, 'PNG'); // explicit PNG
    @unlink($tmp);
    $pdf->Ln($imgWidth + 4);

    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(0, 4, 'Scan the QR code or open this link to view your queue details:', 0, 'C');
    $pdf->SetTextColor(0, 70, 140);
    $pdf->MultiCell(0, 4, $detailsUrl, 0, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);
}

$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 5, 'Thank you. Present this at the counter when called.', 0, 1, 'C');

$pdf->Output('I', 'receipt_' . $queue['queue_code'] . '.pdf');
exit;
