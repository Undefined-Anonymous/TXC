<?php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');  // Your Database Host
define('DB_USER', 'root');  // Your Database Username
define('DB_PASS', '12345678');  // Your Database Password
define('DB_NAME', 'TXC-10301');  // Your Database Name

// SMTP Configuration
define('SMTP_HOST', 'smtp.host.com'); // Your SMTP Host
define('SMTP_PORT', 587);  // 587 For TLS, 465 For SSL
define('SMTP_USERNAME', 'user@domain.com');  // Your SMTP Username/Email
define('SMTP_PASSWORD', '12345678'); // Your SMTP Password
define('SMTP_FROM', 'user@domain.com');  // SMTP From
define('SMTP_FROM_NAME', 'TXC');  //  SMTP Email Sender Name
define('SMTP_SECURE', 'tls');  // 'tls' = Unsecure, 'ssl' = Secure
define('SMTP_DEBUG', 0);  // 0 = Off, 1 = Client Messages, 2 = Client And Server Messages


// Create Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

date_default_timezone_set('UTC');

function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_COOKIE['chat_auth'])) {
        header("Location: login.php");
        exit();
    }
    
    if ($_SESSION['user_id'] != $_COOKIE['chat_auth']) {
        session_destroy();
        setcookie('chat_auth', '', time() - 3600, '/');
        header("Location: login.php");
        exit();
    }
    
    global $conn;
    $stmt = $conn->prepare("SELECT is_verified FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || !$user['is_verified']) {
        header("Location: verify_pending.php");
        exit();
    }
}

// PHPMailer Email Function
function sendEmail($to, $subject, $body) {
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server Settings
        $mail->SMTPDebug = SMTP_DEBUG;
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message Could Not Be Sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>