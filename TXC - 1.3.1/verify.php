<?php
include 'config.php';

if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    $current_time = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE verification_token = ? AND verification_expiry > ? AND is_verified = 0");
    $stmt->bind_param("ss", $token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_expiry = NULL WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Email Verified Successfully! You Can Now Login.";
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            setcookie('chat_auth', $user['id'], time() + (86400 * 7), "/");
            
            $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            $_SESSION['profile_pic'] = $user_data['profile_pic'];
            
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['message'] = "Verification Failed. Please Try Again.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Invalid Or Expired Verification Link. Please Register Again.";
        header("Location: register.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>