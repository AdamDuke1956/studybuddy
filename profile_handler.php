<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}

if (isset($_POST['create_profile'])) {
    $user_id = (int)$_SESSION['user_id'];
    if (isset($_POST['user_id']) && (int)$_POST['user_id'] !== $user_id) {
        header('Location: profile_view.php?id=' . $user_id . '&error=' . urlencode('You can only update your own profile'));
        exit;
    }
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $programming_level = in_array($_POST['programming_level'] ?? 'none', ['none','beginner','intermediate','advanced','expert']) ? $_POST['programming_level'] : 'none';
    $good_writing = isset($_POST['good_writing']) ? 1 : 0;
    $leadership = isset($_POST['leadership']) ? 1 : 0;
    $profile_picture = null;

    // ensure profile_picture column exists (attempt to alter table if missing)
    $colCheck = $conn->query("SHOW COLUMNS FROM profiles LIKE 'profile_picture'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE profiles ADD profile_picture VARCHAR(255) NULL");
    }

    // Handle uploaded profile picture
    if (!empty($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['profile_picture'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($f['type'], $allowed)) {
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $safeName = 'p_' . $user_id . '_' . time() . '.' . $ext;
            $target = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($f['tmp_name'], $target)) {
                $profile_picture = $safeName;
            }
        }
    }

    // If no new upload, try to preserve existing picture (when editing)
    if (empty($profile_picture)) {
        $q = $conn->prepare("SELECT profile_picture FROM profiles WHERE user_id = ? LIMIT 1");
        if ($q) {
            $q->bind_param('i', $user_id);
            $q->execute();
            $r = $q->get_result();
            if ($r && $r->num_rows === 1) {
                $row = $r->fetch_assoc();
                if (!empty($row['profile_picture'])) $profile_picture = $row['profile_picture'];
            }
            $q->close();
        }
    }

    // Upsert profile (insert or update)
    $stmt = $conn->prepare("REPLACE INTO profiles (user_id, bio, skills, programming_level, good_writing, leadership, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('isssiis', $user_id, $bio, $skills, $programming_level, $good_writing, $leadership, $profile_picture);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: index.php?success=' . urlencode('Profile created'));
            exit;
        } else {
            $stmt->close();
            header('Location: create_profile.php?error=' . urlencode('Could not save profile'));
            exit;
        }
    } else {
        header('Location: create_profile.php?error=' . urlencode('Server error'));
        exit;
    }
}

header('Location: create_profile.php');
exit;
