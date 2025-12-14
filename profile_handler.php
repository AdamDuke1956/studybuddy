<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}

if (isset($_POST['create_profile'])) {
    $user_id = $_SESSION['user_id'];
    $bio = trim($_POST['bio']);
    $skills = trim($_POST['skills']);
    $programming_level = in_array($_POST['programming_level'] ?? 'none', ['none','beginner','intermediate','advanced','expert']) ? $_POST['programming_level'] : 'none';
    $good_writing = isset($_POST['good_writing']) ? 1 : 0;
    $leadership = isset($_POST['leadership']) ? 1 : 0;

    // Upsert profile (insert or update)
    $stmt = $conn->prepare("REPLACE INTO profiles (user_id, bio, skills, programming_level, good_writing, leadership) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('isssii', $user_id, $bio, $skills, $programming_level, $good_writing, $leadership);
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
