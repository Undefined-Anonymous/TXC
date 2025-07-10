<?php
include 'config.php';

if (!isset($_SESSION['temp_user'])) {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['temp_user'];
$error = '';
$success = '';

if (isset($_POST['resend'])) {
    $stmt = $conn->prepare("SELECT email, verification_token FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?token=" . $user['verification_token'];
        $subject = "Verify Your Email Address";
        $message = "
            <html>
            <head><title>Email Verification</title></head>
            <body>
                <h2>Welcome To TXC!</h2>
                <p>Please Click The Link Below To Verify Your Email Address:</p>
                <p><a href='$verification_link'>Verify Email</a></p>
                <p>This Link Will Expire In 24 Hours.</p>
                <b>TXC</b>
            </body>
            </html>
        ";
        
        if (sendEmail($user['email'], $subject, $message)) {
            $success = "Verification Email Resent Successfully!";
        } else {
            $error = "Failed To Resend Verification Email. Please Try Again Later.";
        }
    } else {
        $error = "User Not Found. Please Register Again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - TXC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style><?php include 'Assets/CSS/styles.css'; ?></style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <i class="fa fa-envelope-o logo-icon"></i>
                <h1>Email Verification Required</h1>
                <p>We've Sent a Verification Link To Your Email</p>
            </div>
            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php elseif ($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            <div class="verify-info">
                <p>Hello <strong><?= htmlspecialchars($username) ?></strong>, Please Check Your Email Inbox And Click The Verification Link We Sent You.</p>
                <p>The Link Will Expire In 24 Hours.</p>
                <p>If You Didn't Receive The Email, Check Your Spam/Junk Folder Or:</p>
                <form method="POST">
                    <button type="submit" name="resend" class="btn"><i class="fa fa-refresh"></i> Resend Verification Email</button>
                </form>
            </div>
            <div class="auth-footer">
                <a href="login.php"><i class="fa fa-arrow-left"></i> Back To Login</a>
            </div>
        </div>
    </div>
</body>
</html>