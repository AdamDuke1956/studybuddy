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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dash-grid{ display:grid; grid-template-columns: 1fr 360px; gap:24px; width:100%; max-width:1100px; margin:0 auto; }
        .panel{ background:var(--card-light); border-radius:12px; padding:18px; box-shadow:0 12px 30px rgba(0,0,0,0.06); }
        body.dark-mode .panel{ background:var(--card-dark); }
        .assign-list{ display:flex; flex-direction:column; gap:8px; }
        .assign-list label{ display:flex; align-items:center; gap:10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-5">
            <a class="navbar-brand" href="index.php"><i class="fas fa-book-open"></i> STUDYBUDDY</a>
            <div class="d-flex align-items-center gap-3">
                <button id="themeToggle" title="Toggle theme"><i class="fas fa-moon"></i></button>
                <a href="profiles_list.php" class="btn btn-sm btn-light">Browse Profiles</a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Settings</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="create_profile.php">Edit profile</a></li>
                        <li><a class="dropdown-item" href="auth.php">Change email/password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div style="padding:40px 20px;">
        <div class="dash-grid">
            <div>
                <div class="panel">
                    <h3>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                    <p>Quick actions to get you collaborating faster.</p>
                    <div style="display:flex; gap:12px; margin-top:12px;">
                        <a href="profiles_list.php" class="btn btn-purple">Find Members</a>
                        <a href="create_profile.php" class="btn btn-outline-secondary">Edit Profile</a>
                    </div>
                </div>

                <div class="panel" style="margin-top:18px;">
                    <h4>Assignments Checklist</h4>
                    <div class="assign-list" id="assignList">
                        <label><input type="checkbox" data-id="a1"> Research literature review</label>
                        <label><input type="checkbox" data-id="a2"> Proposal draft</label>
                        <label><input type="checkbox" data-id="a3"> Methodology write-up</label>
                    </div>
                </div>
            </div>

            <aside>
                <div class="panel">
                    <h5>Your Profile</h5>
                    <p><a href="profile_view.php?id=<?php echo (int)$_SESSION['user_id']; ?>">View my profile</a></p>
                    <hr>
                    <h6>Shortcuts</h6>
                    <ul>
                        <li><a href="profiles_list.php">Browse Members</a></li>
                        <li><a href="create_profile.php">Edit profile</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme toggle (simple)
        const themeBtn = document.getElementById('themeToggle');
        if (localStorage.getItem('sb-theme') === 'dark') document.body.classList.add('dark-mode');
        themeBtn.addEventListener('click', ()=>{
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('sb-theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });

        // Assignments persistence
        const assignList = document.getElementById('assignList');
        assignList.querySelectorAll('input[type=checkbox]').forEach(cb=>{
            const id = cb.dataset.id;
            cb.checked = localStorage.getItem('assign_'+id) === '1';
            cb.addEventListener('change', ()=> localStorage.setItem('assign_'+id, cb.checked ? '1' : '0'));
        });
    </script>
</body>
</html>
