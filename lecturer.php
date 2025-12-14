<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'lecturer') {
    header('Location: loginpage.php?error=' . urlencode('Please log in as a lecturer'));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lecturer Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Lecturer)</h1>
    <p>This is a placeholder lecturer page. Create your lecturer dashboard here.</p>
    <p><a href="logout.php">Log out</a></p>
</body>
</html>
