<?php
// try multiple possible autoload locations (vendor lives inside backend/vendor in this project)
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
    echo "PHPMailer autoload not found. Run 'composer require phpmailer/phpmailer' in project root or place PHPMailer under backend/vendor or backend/PHPMailer.";
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'reporepo2004@gmail.com';
  $mail->Password = 'dzvtamkeqxgixavv'; // use App Password (no spaces)
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port = 465;
  $mail->SMTPDebug = 2;
  $mail->Debugoutput = 'echo';
  $mail->setFrom('reporepo@gmail.com','Test');
  $mail->addAddress('ricksiv0107@gmail.com','Recipient');
  $mail->Subject = 'PHPMailer test';
  $mail->Body = 'Test message';
  $mail->send();
  echo 'Sent';
} catch (Exception $e) {
  echo 'Error: ' . $e->getMessage();
}
