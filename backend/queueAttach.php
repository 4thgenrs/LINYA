<?php
// queueAttach.php
// Accepts multipart POST: queue_id + attachment
// Sends uploaded file via SMTP to the queue's customer_email using PHPMailer
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../linya_db/config.php'; // provides $conn

// Load PHPMailer (prefer composer) - try multiple possible autoload locations
$possible = [
    __DIR__ . '/vendor/autoload.php',        // backend/vendor/autoload.php (matches your tree)
    __DIR__ . '/../vendor/autoload.php',     // project/vendor/autoload.php (fallback)
];

$loaded = false;
foreach ($possible as $p) {
    if (file_exists($p)) {
        require $p;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    // fallback to direct PHPMailer sources inside vendor/phpmailer/phpmailer
    $base = __DIR__ . '/vendor/phpmailer/phpmailer/src';
    if (file_exists($base . '/PHPMailer.php')) {
        require_once $base . '/Exception.php';
        require_once $base . '/PHPMailer.php';
        require_once $base . '/SMTP.php';
        $loaded = true;
    }
}

if (!$loaded) {
    // last resort: try legacy backend/PHPMailer folder
    $legacy = __DIR__ . '/PHPMailer/src';
    if (file_exists($legacy . '/PHPMailer.php')) {
        require_once $legacy . '/Exception.php';
        require_once $legacy . '/PHPMailer.php';
        require_once $legacy . '/SMTP.php';
        $loaded = true;
    }
}

if (!$loaded) {
    echo json_encode(['success' => false, 'error' => "PHPMailer not found. Run 'composer install' in project root or place PHPMailer under backend/vendor or backend/PHPMailer."]);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    if (!isset($_POST['queue_id']) || empty($_FILES['attachment'])) {
        echo json_encode(['success' => false, 'error' => 'Missing queue_id or attachment']);
        exit;
    }
    $queue_id = intval($_POST['queue_id']);
    if ($queue_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid queue id']);
        exit;
    }

    // fetch queue row
    $stmt = $conn->prepare("SELECT queue_code, customer_email, customer_name FROM queues WHERE queue_id = ?");
    if (!$stmt) throw new Exception('DB prepare failed');
    $stmt->bind_param('i', $queue_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $queue = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$queue) {
        echo json_encode(['success' => false, 'error' => 'Queue not found']);
        exit;
    }

    $toEmail = trim($queue['customer_email'] ?? '');
    if ($toEmail === '') {
        echo json_encode(['success' => false, 'error' => 'No email address available for this queue']);
        exit;
    }

    // validate uploaded file
    $file = $_FILES['attachment'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'File upload error: ' . $file['error']]);
        exit;
    }
    // optional: file size / type checks
    $maxBytes = 10 * 1024 * 1024; // 10 MB
    if ($file['size'] > $maxBytes) {
        echo json_encode(['success' => false, 'error' => 'File too large (max 10 MB)']);
        exit;
    }

    $tmpPath = $file['tmp_name'];  
    $origName = basename($file['name']);

    // SMTP configuration
    $smtpHost = 'smtp.gmail.com';
    $smtpPort = 587; // STARTTLS
    $smtpUser = 'reporepo2004@gmail.com';
    $smtpPass = 'dzvtamkeqxgixavv'; // 16-char App Password (no spaces)
    // Make From the same as the authenticated user to avoid Gmail rejecting the message
    $smtpFrom = $smtpUser;
    $smtpFromName = 'LINYA Kiosk';

    // Build message body
    $queueCode = $queue['queue_code'] ?? ("#{$queue_id}");
    $customerName = $queue['customer_name'] ?? '';
    $subject = "Soft copy request for {$queueCode}";
    $body = "Hello" . ($customerName ? " {$customerName}" : "") . ",\n\n";
    $body .= "This is/are your soft copy request.\n\n";
    $body .= "Reference: {$queueCode}\n\n";
    $body .= "If you did not request this, please contact the office.\n\n";
    $body .= "Regards,\nLINYA";

    // send with PHPMailer
    $mail = new PHPMailer(true);
    // enable exceptions for detailed errors
    try {
        // SMTP
        $mail->isSMTP();
        $mail->SMTPDebug = 3;                // increase temporarily for troubleshooting
        $mail->Debugoutput = 'error_log';    // write debug to Apache error log
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = str_replace(' ', '', $smtpPass);
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // for port 587
        $mail->Port = $smtpPort;
        $mail->SMTPOptions = [
          'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
          ],
        ];

        $mail->setFrom($smtpFrom, $smtpFromName);
        $mail->addAddress($toEmail, $customerName ?: null);
        // reply-to optionally set to office/staff email
        if (isset($_SESSION['user_name'])) {
            $mail->addReplyTo($smtpFrom, $_SESSION['user_name']);
        }

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $body;
        // attach the uploaded file directly from tmp path
        $mail->addAttachment($tmpPath, $origName);

        // send
        if (!$mail->send()) {
            throw new Exception('Mail send failed: ' . $mail->ErrorInfo);
        }

        // success
        echo json_encode(['success' => true, 'file' => $origName]);
        exit;
    } catch (Exception $e) {
        // mail error
        error_log('Mail error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to send email: ' . $e->getMessage()]);
        exit;
    }
} catch (Exception $e) {
    error_log('queueAttach exception: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
}
?>