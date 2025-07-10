<?php
include 'config.php';
checkAuth();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$profile_pic = $_SESSION['profile_pic'];

if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('chat_auth', '', time() - 3600, '/');
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
        $message = sanitize($_POST['message']);
        $reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : null;
        $attachment = null;
        
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $target_dir = "uploads/";
            $fileType = strtolower(pathinfo($_FILES["attachment"]["name"], PATHINFO_EXTENSION));
            $attachment = uniqid() . '.' . $fileType;
            move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_dir . $attachment);
        }
        
        $stmt = $conn->prepare("INSERT INTO messages (user_id, message, attachment, reply_to) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $message, $attachment, $reply_to);
        $stmt->execute();
    }
    
    if (isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] == 0) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["avatar_upload"]["name"], PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["avatar_upload"]["tmp_name"]);
        
        if ($check !== false) {
            $new_filename = uniqid() . '.' . $imageFileType;
            if (move_uploaded_file($_FILES["avatar_upload"]["tmp_name"], $target_dir . $new_filename)) {
              
                if ($profile_pic != 'default.png') {
                    @unlink($target_dir . $profile_pic);
                }
                
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $new_filename, $user_id);
                $stmt->execute();
                
                $_SESSION['profile_pic'] = $new_filename;
                $profile_pic = $new_filename;
            }
        }
    }
    
    if (isset($_POST['delete_message']) && $user_id == 1) {
        $message_id = (int)$_POST['delete_message'];
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
    }
    
    header("Location: index.php");
    exit();
}

$messages = [];
$query = "
    SELECT m.id, m.user_id, m.message, m.attachment, m.reply_to, m.created_at, 
           u.username, u.profile_pic, 
           rm.message AS reply_message, rm.user_id AS reply_user_id, ru.username AS reply_username
    FROM messages m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN messages rm ON m.reply_to = rm.id
    LEFT JOIN users ru ON rm.user_id = ru.id
    ORDER BY m.created_at ASC
";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TXC - Beta 1.3.1</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style><?php include '/Assets/CSS/styles.css'; ?></style>
</head>
<body>
    <div class="chat-container">
        <header class="chat-header">
            <div class="logo">
                <i class="fa fa-comments logo-icon"></i>
                <h1>TXC</h1>
            </div>
            <div class="user-info">
                <div class="avatar-upload">
                    <img src="uploads/<?= $profile_pic ?>" alt="<?= $username ?>" class="profile-pic">
                    <form method="POST" enctype="multipart/form-data" class="avatar-form">
                        <input type="file" name="avatar_upload" id="avatar_upload" accept="image/*">
                        <label for="avatar_upload" class="avatar-edit"><i class="fa fa-camera"></i></label>
                    </form>
                </div>
                <span><?= htmlspecialchars($username) ?></span>
                <a href="?logout" class="logout-btn"><i class="fa fa-sign-out"></i> Logout</a>
            </div>
        </header>
        
        <main class="chat-main">
            <div class="chat-messages" id="chat-messages">
                <?php foreach ($messages as $msg): ?>
                    <div class="message" id="msg-<?= $msg['id'] ?>">
                        <div class="message-header">
                            <img src="uploads/<?= $msg['profile_pic'] ?>" alt="<?= $msg['username'] ?>" class="profile-pic">
                            <span class="username"><?= htmlspecialchars($msg['username']) ?></span>
                            <span class="time"><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
                            <?php if ($user_id == 1): ?>
                                <form method="POST" class="delete-form">
                                    <input type="hidden" name="delete_message" value="<?= $msg['id'] ?>">
                                    <button type="submit" class="delete-btn"><i class="fa fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($msg['reply_to']): ?>
                            <div class="reply-preview">
                                <i class="fa fa-reply"></i>
                                <span class="reply-user"><?= htmlspecialchars($msg['reply_username']) ?></span>
                                <span class="reply-text"><?= htmlspecialchars(substr($msg['reply_message'], 0, 50)) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            
                            <?php if ($msg['attachment']): ?>
                                <div class="attachment">
                                    <?php 
                                    $ext = pathinfo($msg['attachment'], PATHINFO_EXTENSION);
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <img src="uploads/<?= $msg['attachment'] ?>" alt="Attachment" class="attachment-img">
                                    <?php else: ?>
                                        <a href="uploads/<?= $msg['attachment'] ?>" download class="attachment-file">
                                            <i class="fa fa-paperclip"></i> <?= $msg['attachment'] ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="message-actions">
                            <button class="reply-btn" data-msgid="<?= $msg['id'] ?>"><i class="fa fa-reply"></i> Reply</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form class="chat-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="reply_to" id="reply_to" value="">
                <div class="reply-preview" id="reply-preview"></div>
                <div class="form-group">
                    <input type="text" name="message" placeholder="Type Here..." required>
                    <label for="attachment" class="attachment-label">
                        <i class="fa fa-paperclip"></i>
                        <input type="file" id="attachment" name="attachment" style="display: none;">
                    </label>
                    <button type="submit" class="send-btn"><i class="fa fa-paper-plane"></i></button>
                </div>
            </form>
        </main>
    </div>
    
    <script src="Assets/JS/main.js"></script>
</body>
</html>