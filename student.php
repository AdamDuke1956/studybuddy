<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header('Location: loginpage.php?error=' . urlencode('Please log in as a student'));
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT u.full_name, u.email, p.user_id AS profile_owner, p.bio, p.skills, p.programming_level, p.good_writing, p.leadership, p.profile_picture FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ? LIMIT 1");
if (!$stmt) {
    die('Server error');
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$profile = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$profile || $profile['profile_owner'] === null) {
    header('Location: create_profile.php?error=' . urlencode('Create your profile to access the dashboard'));
    exit;
}

$traits = [];
if ((int)$profile['good_writing'] === 1) { $traits[] = 'Good at writing'; }
if ((int)$profile['leadership'] === 1) { $traits[] = 'Leadership'; }
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
        @media (max-width: 1024px){ .dash-grid{ grid-template-columns:1fr; } }
        .nav-gear-btn{ background:rgba(255,255,255,0.2); border:none; color:#fff; padding:8px 10px; border-radius:10px; transition:all .2s ease; }
        .nav-gear-btn:hover{ background:rgba(255,255,255,0.28); transform:translateY(-1px); }
        .nav-gear-btn:focus{ outline:none; box-shadow:none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-5">
            <a class="navbar-brand" href="index.php"><i class="fas fa-book-open"></i> STUDYBUDDY</a>
            <div class="collapse navbar-collapse justify-content-center">
                <div class="navbar-nav">
                    <a class="nav-link active" href="student.php">DASHBOARD</a>
                    <a class="nav-link" href="profiles_list.php">BROWSE</a>
                    <a class="nav-link" href="profile_view.php?id=<?php echo $userId; ?>">MY PROFILE</a>
                </div>
            </div>
            <div class="nav-icons" style="gap:12px;">
                <button id="themeToggle"><i class="fas fa-moon"></i></button>
                <div class="dropdown">
                    <button class="nav-gear-btn dropdown-toggle" data-bs-toggle="dropdown" aria-label="User menu"><i class="fas fa-cog"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="edit_profile.php">Edit profile</a></li>
                        <li><a class="dropdown-item" href="account_settings.php">Account settings</a></li>
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
                    <p class="mb-3">Jump back into collaborating with classmates.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="profiles_list.php" class="btn btn-purple">Find members</a>
                        <a href="profile_view.php?id=<?php echo $userId; ?>" class="btn btn-outline-secondary">View my profile</a>
                        <a href="edit_profile.php" class="btn btn-outline-secondary">Edit profile</a>
                    </div>
                </div>

                <div class="panel" style="margin-top:18px;">
                    <h4>Your profile snapshot</h4>
                    <div class="d-flex gap-3 align-items-start">
                        <?php if (!empty($profile['profile_picture'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile" style="width:78px;height:78px;object-fit:cover;border-radius:10px;" />
                        <?php else: ?>
                            <div style="width:78px;height:78px;border-radius:10px;background:linear-gradient(135deg,#f0f0f0,#e8e8e8);display:flex;align-items:center;justify-content:center;font-weight:700;color:#666;">
                                <?php echo strtoupper(substr($profile['full_name'],0,1)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="fw-bold">Programming level: <?php echo htmlspecialchars($profile['programming_level'] ?? 'none'); ?></div>
                            <div>Skills: <?php echo htmlspecialchars($profile['skills'] ?? ''); ?></div>
                            <?php if (!empty($traits)): ?>
                                <div>Traits: <?php echo htmlspecialchars(implode(', ', $traits)); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="mt-3 mb-0" style="white-space:pre-line;"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></p>
                </div>

                <div class="panel" style="margin-top:18px;">
                    <h4>Assignments checklist</h4>
                    <div class="assign-list" id="assignList">
                        <label><input type="checkbox" data-id="a1"> Research literature review</label>
                        <label><input type="checkbox" data-id="a2"> Proposal draft</label>
                        <label><input type="checkbox" data-id="a3"> Methodology write-up</label>
                    </div>
                </div>
            </div>

            <aside>
                <div class="panel">
                    <h5>Quick links</h5>
                    <ul class="mb-0">
                        <li><a href="profiles_list.php">Browse members</a></li>
                        <li><a href="profile_view.php?id=<?php echo $userId; ?>">View my profile</a></li>
                        <li><a href="edit_profile.php">Edit profile</a></li>
                        <li><a href="account_settings.php">Account settings</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const themeBtn = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('sb-theme') || 'light';
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            themeBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        const playBeep = () => {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = 660;
                osc.connect(gain);
                gain.connect(ctx.destination);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.12);
                osc.start();
                osc.stop(ctx.currentTime + 0.12);
            } catch (e) { /* ignore audio errors */ }
        };
        themeBtn.addEventListener('click', ()=>{
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('sb-theme', isDark ? 'dark' : 'light');
            themeBtn.innerHTML = isDark ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
            playBeep();
        });

        const assignList = document.getElementById('assignList');
        assignList.querySelectorAll('input[type=checkbox]').forEach(cb=>{
            const id = cb.dataset.id;
            cb.checked = localStorage.getItem('assign_'+id) === '1';
            cb.addEventListener('change', ()=> localStorage.setItem('assign_'+id, cb.checked ? '1' : '0'));
        });
    </script>
</body>
</html>
