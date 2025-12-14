<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="sb-card" style="max-width:700px;">
            <h3>Create Your Profile</h3>
            <p class="text-center">Tell others what you're good at so they can collaborate with you.</p>
            <form action="profile_handler.php" method="post" enctype="multipart/form-data">
                <div class="sb-input-group">
                    <i class="fas fa-pen"></i>
                    <textarea name="bio" placeholder="Short bio / description" rows="4" style="padding:15px 15px 15px 45px; width:100%; border-radius:12px; border:2px solid transparent; resize:vertical;"></textarea>
                </div>

                <div class="sb-input-group">
                    <i class="fas fa-code"></i>
                    <input type="text" name="skills" placeholder="Skills (comma separated) e.g. Python, LaTeX, Research" />
                </div>

                <div class="sb-input-group">
                    <i class="fas fa-layer-group"></i>
                    <select name="programming_level" style="padding:12px 15px 12px 45px; border-radius:12px; width:100%; border:2px solid transparent;">
                        <option value="none">No programming</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>

                <div style="display:flex; gap:20px; margin:8px 0 18px 0;">
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="good_writing"> Good at writing reports</label>
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="leadership"> Leading groups</label>
                </div>

                <div class="sb-input-group">
                    <i class="fas fa-image"></i>
                    <input type="file" name="profile_picture" accept="image/*" />
                </div>
                <button type="submit" name="create_profile" class="btn-purple">Save Profile</button>
            </form>
        </div>
    </div>
</body>
</html>
