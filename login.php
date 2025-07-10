<?php

include 'config.php';

if (isset($_SESSION['user_id']) && isset($_COOKIE['chat_auth'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, profile_pic, is_verified FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified']) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                
                setcookie('chat_auth', $user['id'], time() + (86400 * 7), "/");
                
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['temp_user'] = $username;
                header("Location: verify_pending.php");
                exit();
            }
        } else {
            $error = "Invalid Password.";
        }
    } else {
        $error = "Username Not Found.";
    }
}

// Check for verification message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chat App</title>
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
                <p>Welcome back!</p>
            </div>
            <?php if ($message): ?>
                <div class="alert success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label><i class="fa fa-user"></i> Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label><i class="fa fa-lock"></i> Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn"><i class="fa fa-sign-in"></i> Login</button>
            </form>
            <div class="auth-footer">
                Don't Have An Account? <a href="register.php">Register Here</a>
            </div>
        </div>
    </div>
</body>
</html>