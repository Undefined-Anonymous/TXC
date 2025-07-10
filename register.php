<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Handle profile picture upload
            $profile_pic = 'default.png';
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $target_dir = "uploads/";
                $imageFileType = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
                $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
                
                if ($check !== false) {
                    $profile_pic = uniqid() . '.' . $imageFileType;
                    if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_dir . $profile_pic)) {
                        $profile_pic = 'default.png';
                    }
                }
            }
            
            // Generate verification token
            $verification_token = md5(uniqid(rand(), true));
            $verification_expiry = date('Y-m-d H:i:s', strtotime('+1 day'));
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_pic, verification_token, verification_expiry) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $password_hash, $profile_pic, $verification_token, $verification_expiry);
            
            if ($stmt->execute()) {
                // Send verification email
                $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?token=" . $verification_token;
                $subject = "Verify Your Email Address";
                $message = "
                    <html>
                    <head><title>Email Verification</title></head>
                    <body>
                        <h2>Welcome to Chat App!</h2>
                        <p>Please click the link below to verify your email:</p>
                        <p><a href='$verification_link'>Verify Email</a></p>
                        <p>Link expires in 24 hours.</p>
                    </body>
                    </html>
                ";
                
                if (sendEmail($email, $subject, $message)) {
                    $_SESSION['temp_user'] = $username;
                    header("Location: verify_pending.php");
                    exit();
                } else {
                    $error = "Registration successful but email sending failed. Contact support.";
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TXC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style><?php include 'Assets/CSS/styles.css'; ?></style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <i class="fa fa-comments logo-icon"></i>
                <h1>TXC</h1>
                <p>Create Your Account</p>
            </div>
            <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fa fa-user"></i> Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-lock"></i> Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-lock"></i> Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-camera"></i> Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*">
                </div>
                <button type="submit" class="btn"><i class="fa fa-user-plus"></i> Register</button>
            </form>
            <div class="auth-footer">
                Already Have An Account? <a href="login.php">Login Here</a>
            </div>
        </div>
    </div>
</body>
</html>