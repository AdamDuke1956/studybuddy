<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userRole = $isLoggedIn ? ($_SESSION['user_role'] ?? 'student') : null;
$dashboardUrl = $userRole === 'lecturer' ? 'lecturer.php' : 'student.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>StudyBuddy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero-bg { background: radial-gradient(circle at 20% 20%, rgba(99,102,241,0.14), transparent 35%), radial-gradient(circle at 80% 0%, rgba(59,130,246,0.16), transparent 40%), #0f172a; color:#eef2ff; }
        .hero-cta .btn { min-width:150px; }
        .feature-card { background: #ffffff; border-radius: 14px; padding: 18px; box-shadow: 0 12px 38px rgba(0,0,0,0.08); height:100%; }
        .stat-pill { display:inline-flex; align-items:center; gap:10px; padding:10px 14px; border-radius:999px; background: rgba(255,255,255,0.12); color:#e2e8f0; }
        @media (max-width:768px){ .hero-cta { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4 px-lg-5">
            <a class="navbar-brand" href="index.php"><i class="fas fa-book-open"></i> STUDYBUDDY</a>
            <div class="d-flex align-items-center gap-2">
                <?php if ($isLoggedIn): ?>
                    <span class="text-white d-none d-md-inline">Hi, <?php echo htmlspecialchars($userName); ?></span>
                    <a class="btn btn-sm btn-light" href="<?php echo $dashboardUrl; ?>">Go to dashboard</a>
                    <a class="btn btn-sm btn-outline-light" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-sm btn-light" href="loginpage.php">Login</a>
                    <a class="btn btn-sm btn-outline-light" href="signuppage.php">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero-bg py-5 py-lg-5">
        <div class="container px-4 px-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="stat-pill mb-3"><i class="fas fa-users"></i><span>Match with course mates fast</span></div>
                    <h1 class="display-5 fw-bold mb-3">Find your perfect study buddy and ship assignments together.</h1>
                    <p class="lead mb-4" style="max-width:640px; color:#cbd5e1;">Create a short profile, browse classmates, and collaborate on research, reports, or coding tasks with people who fit your style.</p>
                    <div class="d-flex hero-cta gap-2">
                        <?php if ($isLoggedIn): ?>
                            <a class="btn btn-light btn-lg" href="<?php echo $dashboardUrl; ?>">Open dashboard</a>
                            <a class="btn btn-outline-light btn-lg" href="profiles_list.php">Browse profiles</a>
                        <?php else: ?>
                            <a class="btn btn-light btn-lg" href="signuppage.php">Get started</a>
                            <a class="btn btn-outline-light btn-lg" href="loginpage.php">I already have an account</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="feature-card" style="background: #111827; color:#e5e7eb; border:1px solid rgba(255,255,255,0.08);">
                        <h5 class="mb-3">How it works</h5>
                        <ol class="mb-0" style="line-height:1.7;">
                            <li>Create an account and fill your skills.</li>
                            <li>Browse classmates, read their profiles, and connect.</li>
                            <li>Share tasks, divide work, and keep everyone in sync.</li>
                        </ol>
                        <hr class="text-secondary">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-success">Coding</span>
                            <span class="badge bg-info text-dark">Writing</span>
                            <span class="badge bg-primary">Leadership</span>
                            <span class="badge bg-warning text-dark">Research</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-primary"><i class="fas fa-id-badge"></i></span>
                            <h5 class="mb-0">Profile in minutes</h5>
                        </div>
                        <p class="mb-0">Share your strengths, preferred roles, and availability so others know how you collaborate best.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-success"><i class="fas fa-users"></i></span>
                            <h5 class="mb-0">Smart browsing</h5>
                        </div>
                        <p class="mb-0">Scan classmates by programming level and soft skills to quickly spot a good fit.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-warning text-dark"><i class="fas fa-clipboard-check"></i></span>
                            <h5 class="mb-0">Keep work organized</h5>
                        </div>
                        <p class="mb-0">Use the dashboard checklist to track tasks and keep everyone accountable.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 text-center text-muted">
        <small>Built for quick student collaboration. Need help? <a href="loginpage.php">Log in</a> or <a href="signuppage.php">create an account</a>.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>