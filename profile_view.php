<?php
include 'db_conn.php';
// view profile by ?id=
$view_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($view_id <= 0) {
    echo 'Invalid profile id';
    exit;
}

$stmt = $conn->prepare("SELECT u.full_name, u.role, p.bio, p.skills, p.programming_level, p.good_writing, p.leadership, p.created_at FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ? LIMIT 1");
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
            <h3><?php echo htmlspecialchars($row['full_name']); ?></h3>
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
        </div>
    </div>
</body>
</html>
