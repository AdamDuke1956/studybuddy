<?php
session_start();
require 'db_conn.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}
$userId = (int)$_SESSION['user_id'];
$collabId = isset($_GET['collab_id']) ? (int)$_GET['collab_id'] : 0;
if ($collabId <= 0) { echo 'Invalid collaboration'; exit; }

// membership check
$check = $conn->prepare('SELECT c.assignment_title, c.status FROM collaborations c JOIN collaboration_members m ON m.collaboration_id = c.id WHERE c.id = ? AND m.user_id = ? LIMIT 1');
$check->bind_param('ii', $collabId, $userId);
$check->execute();
$res = $check->get_result();
$collab = $res ? $res->fetch_assoc() : null;
$check->close();
if (!$collab) { echo 'You are not part of this collaboration'; exit; }

$msgStmt = $conn->prepare('SELECT cm.*, u.full_name FROM collaboration_messages cm JOIN users u ON cm.sender_id = u.id WHERE cm.collaboration_id = ? ORDER BY cm.created_at ASC');
$msgStmt->bind_param('i', $collabId);
$msgStmt->execute();
$messages = $msgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$msgStmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collab Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-box{height:420px;overflow-y:auto;background:var(--card-light);border-radius:12px;padding:14px;box-shadow:0 8px 24px rgba(0,0,0,0.08);}        
        body.dark-mode .chat-box{background:var(--card-dark);}        
        .msg{margin-bottom:12px;}
        .msg .meta{font-size:0.8rem;color:#888;}
        body.dark-mode .msg .meta{color:#aaa;}
        .bubble{padding:10px 14px;border-radius:12px;display:inline-block;max-width:80%;}
        .me{background:linear-gradient(135deg,#6f42c1,#5a32a3);color:#fff;}
        .other{background:rgba(111,66,193,0.1);}
    </style>
</head>
<body>
<div class="main-wrapper" style="align-items:flex-start;">
    <div class="sb-card" style="max-width:900px;width:100%;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 style="margin-bottom:0;">Chat: <?php echo htmlspecialchars($collab['assignment_title']); ?></h3>
            <a class="btn btn-outline-secondary btn-sm" href="collaborations.php?collab=<?php echo $collabId; ?>">Back</a>
        </div>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div><?php endif; ?>

        <div class="chat-box" id="chatBox">
            <?php foreach($messages as $m): $isMe = ((int)$m['sender_id'] === $userId); ?>
                <div class="msg text-<?php echo $isMe ? 'end' : 'start'; ?>">
                    <div class="bubble <?php echo $isMe ? 'me' : 'other'; ?>">
                        <?php echo nl2br(htmlspecialchars($m['body'])); ?>
                    </div>
                    <div class="meta"><?php echo htmlspecialchars($m['full_name']); ?> • <?php echo htmlspecialchars($m['created_at']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <form action="collab_actions.php" method="post" class="mt-3">
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="collab_id" value="<?php echo $collabId; ?>">
            <div class="input-group">
                <textarea class="form-control" name="body" rows="2" placeholder="Message" required></textarea>
                <button class="btn btn-purple" type="submit">Send</button>
            </div>
        </form>
    </div>
</div>
<script>
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>
</body>
</html>
