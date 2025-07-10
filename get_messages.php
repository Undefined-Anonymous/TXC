<?php
include 'config.php';
checkAuth();

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

while ($msg = $result->fetch_assoc()): ?>
    <div class="message" id="msg-<?= $msg['id'] ?>">
        <div class="message-header">
            <img src="uploads/<?= $msg['profile_pic'] ?>" alt="<?= $msg['username'] ?>" class="profile-pic">
            <span class="username"><?= htmlspecialchars($msg['username']) ?></span>
            <span class="time"><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
            <?php if ($_SESSION['user_id'] == 1): ?>
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
<?php endwhile; ?>