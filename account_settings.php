<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}
include 'db_conn.php';
$uid = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT email, full_name FROM users WHERE id = ? LIMIT 1');
$email = '';
$name = '';
if ($stmt) {
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r && $r->num_rows === 1) {
        $row = $r->fetch_assoc();
        $email = $row['email'];
        $name = $row['full_name'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="sb-card" style="max-width:700px;">
            <h3>Account Settings</h3>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
            <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div><?php endif; ?>

            <h5>Change email</h5>
            <form action="auth.php" method="post">
                <div class="sb-input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="new_email" value="<?php echo htmlspecialchars($email); ?>" placeholder="New email" />
                </div>
                <div class="sb-input-group">
                    <i class="fas fa-key"></i>
                    <input type="password" name="current_password" placeholder="Your current password" />
                </div>
                <button type="submit" name="change_email" class="btn-purple">Update email</button>
            </form>

            <hr>
            <h5>Change password</h5>
            <form action="auth.php" method="post">
                <div class="sb-input-group">
                    <i class="fas fa-key"></i>
                    <input type="password" name="current_password" placeholder="Current password" />
                </div>
                <div class="sb-input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" placeholder="New password" />
                </div>
                <div class="sb-input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" />
                </div>
                <button type="submit" name="change_password" class="btn-purple">Change password</button>
            </form>

            <p class="text-center"><a href="edit_profile.php">Back to profile</a></p>
        </div>
    </div>
</body>
</html>