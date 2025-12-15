<?php
session_start();
include 'db_conn.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}
$user_id = (int)$_SESSION['user_id'];
if (isset($_GET['id']) && (int)$_GET['id'] !== $user_id) {
    header('Location: profile_view.php?id=' . $user_id . '&error=' . urlencode('You can only edit your own profile'));
    exit;
}
$stmt = $conn->prepare("SELECT bio, skills, programming_level, good_writing, leadership, profile_picture FROM profiles WHERE user_id = ? LIMIT 1");
$bio = $skills = $programming_level = '';
$good_writing = $leadership = 0;
$profile_picture = null;
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $bio = $row['bio'];
        $skills = $row['skills'];
        $programming_level = $row['programming_level'];
        $good_writing = (int)$row['good_writing'];
        $leadership = (int)$row['leadership'];
        $profile_picture = $row['profile_picture'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <div style="max-width:700px; margin:0 auto 12px auto; display:flex; justify-content:space-between; align-items:center;">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-label="User menu"><i class="fas fa-gear"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile_view.php?id=<?php echo $user_id; ?>">View profile</a></li>
                    <li><a class="dropdown-item" href="account_settings.php">Account settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
                </ul>
            </div>
        </div>
        <div class="sb-card" style="max-width:700px;">
            <h3>Edit Your Profile</h3>
            <form action="profile_handler.php" method="post" enctype="multipart/form-data">
                <div class="sb-input-group">
                    <i class="fas fa-pen"></i>
                    <textarea name="bio" placeholder="Short bio / description" rows="4" style="padding:15px 15px 15px 45px; width:100%; border-radius:12px; border:2px solid transparent; resize:vertical;"><?php echo htmlspecialchars($bio); ?></textarea>
                </div>

                <div class="sb-input-group">
                    <i class="fas fa-code"></i>
                    <input type="text" name="skills" placeholder="Skills (comma separated) e.g. Python, LaTeX, Research" value="<?php echo htmlspecialchars($skills); ?>" />
                </div>

                <div class="sb-input-group">
                    <i class="fas fa-layer-group"></i>
                    <select name="programming_level" style="padding:12px 15px 12px 45px; border-radius:12px; width:100%; border:2px solid transparent;">
                        <option value="none" <?php echo $programming_level=='none' ? 'selected' : ''; ?>>No programming</option>
                        <option value="beginner" <?php echo $programming_level=='beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="intermediate" <?php echo $programming_level=='intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo $programming_level=='advanced' ? 'selected' : ''; ?>>Advanced</option>
                        <option value="expert" <?php echo $programming_level=='expert' ? 'selected' : ''; ?>>Expert</option>
                    </select>
                </div>

                <div style="display:flex; gap:20px; margin:8px 0 18px 0;">
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="good_writing" <?php echo $good_writing ? 'checked' : ''; ?>> Good at writing reports</label>
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="leadership" <?php echo $leadership ? 'checked' : ''; ?>> Leading groups</label>
                </div>

                <?php if (!empty($profile_picture)): ?>
                    <div style="margin-bottom:12px; display:flex; gap:12px; align-items:center;">
                        <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile" style="width:88px;height:88px;object-fit:cover;border-radius:10px;" />
                        <div>
                            <div style="font-weight:700;">Current picture</div>
                            <div class="small-muted">Upload a new picture to replace</div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="sb-input-group">
                    <i class="fas fa-image"></i>
                    <input type="file" name="profile_picture" accept="image/*" />
                </div>

                <button type="submit" name="create_profile" class="btn-purple">Save Profile</button>
            </form>
            <p class="text-center"><a href="account_settings.php">Account settings</a></p>
        </div>
    </div>
</body>
</html>