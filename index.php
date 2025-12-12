<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - Study Buddy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-5">
            <a class="navbar-brand" href="#"><i class="fas fa-book-open"></i> STUDYBUDDY</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white">Hello, <?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5 text-center">
        <h1>Welcome to Study Buddy Dashboard</h1>
        <p>This is where the matching system will go later.</p>
    </div>
</body>
</html>
<?php
} else {
    header("Location: loginpage.php");
    exit();
}
?>