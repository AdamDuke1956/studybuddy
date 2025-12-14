<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header('Location: loginpage.php?error=' . urlencode('Please log in as a student'));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Student)</h1>
    <p>This is a placeholder student page. Create your student dashboard here.</p>
    <p><a href="logout.php">Log out</a></p>
</body>
</html>
