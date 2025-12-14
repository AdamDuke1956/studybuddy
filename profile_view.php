<?php
include 'db_conn.php';
session_start();
// view profile by ?id=
$view_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($view_id <= 0) {
    echo 'Invalid profile id';
    exit;
}

$stmt = $conn->prepare("SELECT u.full_name, u.role, p.bio, p.skills, p.programming_level, p.good_writing, p.leadership, p.profile_picture, p.created_at FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ? LIMIT 1");
if (!$stmt) { echo 'Server error'; exit; }
$stmt->bind_param('i', $view_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) { echo 'Profile not found'; exit; }
$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($row['full_name']); ?> - Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="sb-card" style="max-width:700px;">
            <div style="display:flex; gap:18px; align-items:center;">
                <?php if (!empty($row['profile_picture'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile" style="width:96px;height:96px;border-radius:12px;object-fit:cover;box-shadow:0 6px 20px rgba(0,0,0,0.12);" />
                <?php endif; ?>
                <h3 style="margin:0;"><?php echo htmlspecialchars($row['full_name']); ?></h3>
            </div>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($row['role']); ?></p>
            <p><strong>Programming level:</strong> <?php echo htmlspecialchars($row['programming_level'] ?? 'none'); ?></p>
            <p><strong>Skills:</strong> <?php echo htmlspecialchars($row['skills']); ?></p>
            <p><strong>Traits:</strong>
                <?php
                    $traits = [];
                    if ($row['good_writing']) $traits[] = 'Good at writing';
                    if ($row['leadership']) $traits[] = 'Leadership';
                    echo htmlspecialchars(implode(', ', $traits));
                ?>
            </p>
            <div style="margin-top:20px;">
                <h4>About</h4>
                <p><?php echo nl2br(htmlspecialchars($row['bio'])); ?></p>
            </div>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $view_id): ?>
                <div style="margin-top:18px; text-align:right;">
                    <a href="edit_profile.php" class="btn-purple" style="display:inline-block; padding:10px 16px; text-decoration:none;">Edit profile</a>
                    <a href="account_settings.php" class="btn btn-sm" style="margin-left:8px;">Account settings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
