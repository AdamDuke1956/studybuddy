<?php
include 'db_conn.php';
session_start();
// view profile by ?id=
$view_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($view_id <= 0) {
    echo 'Invalid profile id';
    exit;
}

$stmt = $conn->prepare("SELECT u.full_name, u.role, p.bio, p.skills, p.programming_level, p.good_writing, p.leadership, p.profile_picture, p.created_at FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ? LIMIT 1");
if (!$stmt) { echo 'Server error'; exit; }
$stmt->bind_param('i', $view_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) { echo 'Profile not found'; exit; }
$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($row['full_name']); ?> - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { min-height:100vh; }
        .profile-wrapper { max-width:900px; margin:0 auto; padding:20px; animation:fadeSlideUp 0.6s ease; }
        
        @keyframes fadeSlideUp {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }
        
        .top-controls {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:24px;
            animation:slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from { opacity:0; transform:translateY(-20px); }
            to { opacity:1; transform:translateY(0); }
        }
        
        .profile-hero {
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            border-radius:20px;
            padding:40px;
            margin-bottom:24px;
            position:relative;
            overflow:hidden;
            animation:zoomIn 0.6s ease;
        }
        
        @keyframes zoomIn {
            from { opacity:0; transform:scale(0.9); }
            to { opacity:1; transform:scale(1); }
        }
        
        .profile-hero::before {
            content:'';
            position:absolute;
            top:-50%;
            right:-50%;
            width:200%;
            height:200%;
            background:radial-gradient(circle,rgba(255,255,255,0.1),transparent);
            animation:float 8s ease-in-out infinite;
        }
        
        @keyframes float {
            0%,100% { transform:translate(0,0); }
            50% { transform:translate(-20px,-20px); }
        }
        
        .profile-hero-content { position:relative; z-index:1; display:flex; align-items:center; gap:30px; }
        
        .profile-avatar-large {
            width:140px;
            height:140px;
            border-radius:20px;
            object-fit:cover;
            border:5px solid rgba(255,255,255,0.3);
            box-shadow:0 15px 40px rgba(0,0,0,0.3);
            transition:transform 0.3s ease;
        }
        
        .profile-avatar-large:hover { transform:scale(1.05) rotate(2deg); }
        
        .profile-placeholder-large {
            width:140px;
            height:140px;
            border-radius:20px;
            background:rgba(255,255,255,0.2);
            border:5px solid rgba(255,255,255,0.3);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-weight:700;
            font-size:3rem;
        }
        
        .profile-hero-info { flex:1; color:#fff; }
        .profile-hero-name { font-size:2.5rem; font-weight:700; margin-bottom:10px; text-shadow:0 2px 10px rgba(0,0,0,0.2); }
        .profile-hero-role { font-size:1.2rem; opacity:0.9; display:flex; align-items:center; gap:10px; }
        
        .stats-row {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:20px;
            margin-bottom:24px;
        }
        
        .stat-card {
            background:var(--card-light);
            border-radius:16px;
            padding:20px;
            box-shadow:0 8px 30px rgba(111,66,193,0.12);
            transition:all 0.3s ease;
            animation:slideInUp 0.6s ease;
            animation-fill-mode:both;
        }
        
        body.dark-mode .stat-card {
            background:var(--card-dark);
            box-shadow:0 8px 30px rgba(0,0,0,0.4);
        }
        
        .stat-card:nth-child(1) { animation-delay:0.1s; }
        .stat-card:nth-child(2) { animation-delay:0.2s; }
        .stat-card:nth-child(3) { animation-delay:0.3s; }
        
        @keyframes slideInUp {
            from { opacity:0; transform:translateY(20px); }
            to { opacity:1; transform:translateY(0); }
        }
        
        .stat-card:hover {
            transform:translateY(-5px);
            box-shadow:0 12px 40px rgba(111,66,193,0.2);
        }
        
        body.dark-mode .stat-card:hover {
            box-shadow:0 12px 40px rgba(111,66,193,0.3);
        }
        
        .stat-icon {
            width:50px;
            height:50px;
            border-radius:12px;
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.5rem;
            margin-bottom:12px;
        }
        
        .stat-label { font-size:0.9rem; color:#888; margin-bottom:4px; }
        body.dark-mode .stat-label { color:#aaa; }
        .stat-value { font-size:1.3rem; font-weight:700; color:var(--text-light); }
        body.dark-mode .stat-value { color:var(--text-dark); }
        
        .info-section {
            background:var(--card-light);
            border-radius:16px;
            padding:30px;
            box-shadow:0 8px 30px rgba(111,66,193,0.12);
            animation:slideInUp 0.7s ease;
            animation-delay:0.4s;
            animation-fill-mode:both;
        }
        
        body.dark-mode .info-section {
            background:var(--card-dark);
            box-shadow:0 8px 30px rgba(0,0,0,0.4);
        }
        
        .info-section h4 {
            font-family:'Poppins',sans-serif;
            font-weight:700;
            margin-bottom:20px;
            color:var(--text-light);
        }
        
        body.dark-mode .info-section h4 { color:var(--text-dark); }
        
        .info-row {
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:16px;
            padding:12px;
            border-radius:10px;
            background:rgba(111,66,193,0.05);
            transition:all 0.3s ease;
        }
        
        body.dark-mode .info-row { background:rgba(111,66,193,0.1); }
        .info-row:hover { background:rgba(111,66,193,0.1); transform:translateX(5px); }
        body.dark-mode .info-row:hover { background:rgba(111,66,193,0.15); }
        
        .info-icon {
            width:40px;
            height:40px;
            border-radius:10px;
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        
        .skill-tags { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
        .skill-tag {
            padding:8px 16px;
            border-radius:20px;
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            color:#fff;
            font-size:0.85rem;
            font-weight:600;
            box-shadow:0 4px 10px rgba(111,66,193,0.3);
            transition:all 0.3s ease;
        }
        .skill-tag:hover { transform:translateY(-3px); box-shadow:0 6px 15px rgba(111,66,193,0.4); }
        
        .trait-badge {
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:6px 14px;
            border-radius:20px;
            background:rgba(111,66,193,0.1);
            color:#6f42c1;
            font-size:0.9rem;
            font-weight:600;
            margin-right:8px;
            margin-bottom:8px;
        }
        
        body.dark-mode .trait-badge { background:rgba(111,66,193,0.2); color:#9f7aea; }
        
        .bio-text {
            line-height:1.8;
            color:var(--text-light);
            white-space:pre-line;
        }
        
        body.dark-mode .bio-text { color:var(--text-dark); }
        
        .action-buttons {
            margin-top:24px;
            display:flex;
            gap:12px;
            flex-wrap:wrap;
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <div class="top-controls">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-label="User menu"><i class="fas fa-gear"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="student.php">Dashboard</a></li>
                    <li><a class="dropdown-item" href="edit_profile.php">Edit my profile</a></li>
                    <li><a class="dropdown-item" href="account_settings.php">Account settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-hero">
            <div class="profile-hero-content">
                <?php if (!empty($row['profile_picture'])): ?>
                    <img class="profile-avatar-large" src="uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile" />
                <?php else: ?>
                    <div class="profile-placeholder-large"><?php echo strtoupper(substr($row['full_name'],0,1)); ?></div>
                <?php endif; ?>
                <div class="profile-hero-info">
                    <h1 class="profile-hero-name"><?php echo htmlspecialchars($row['full_name']); ?></h1>
                    <div class="profile-hero-role">
                        <i class="fas fa-user-graduate"></i>
                        <span><?php echo htmlspecialchars($row['role']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-code"></i></div>
                <div class="stat-label">Programming Level</div>
                <div class="stat-value"><?php echo htmlspecialchars(ucfirst($row['programming_level'] ?? 'none')); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="stat-label">Skills Count</div>
                <div class="stat-value"><?php echo count(array_filter(explode(',', $row['skills'] ?? ''))); ?> Skills</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-label">Member Since</div>
                <div class="stat-value"><?php echo date('M Y', strtotime($row['created_at'] ?? 'now')); ?></div>
            </div>
        </div>
        
        <div class="info-section">
            <h4><i class="fas fa-info-circle"></i> Profile Information</h4>
            
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-tools"></i></div>
                <div>
                    <strong>Skills</strong>
                    <div class="skill-tags">
                        <?php
                        $skills = array_filter(array_map('trim', explode(',', $row['skills'] ?? '')));
                        if (empty($skills)) {
                            echo '<span class="skill-tag">No skills listed</span>';
                        } else {
                            foreach($skills as $skill) {
                                echo '<span class="skill-tag">' . htmlspecialchars($skill) . '</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-award"></i></div>
                <div>
                    <strong>Traits & Strengths</strong><br>
                    <?php
                        $traits = [];
                        if ($row['good_writing']) $traits[] = '<span class="trait-badge"><i class="fas fa-pen"></i> Good at writing</span>';
                        if ($row['leadership']) $traits[] = '<span class="trait-badge"><i class="fas fa-users"></i> Leadership</span>';
                        echo empty($traits) ? '<span class="trait-badge"><i class="fas fa-info"></i> No traits listed</span>' : implode('', $traits);
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-user"></i></div>
                <div style="flex:1;">
                    <strong>About</strong>
                    <p class="bio-text mt-2"><?php echo nl2br(htmlspecialchars($row['bio'] ?? 'No bio provided yet.')); ?></p>
                </div>
            </div>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $view_id): ?>
                <div class="action-buttons">
                    <a href="edit_profile.php" class="btn btn-purple"><i class="fas fa-edit"></i> Edit profile</a>
                    <a href="account_settings.php" class="btn btn-outline-secondary"><i class="fas fa-cog"></i> Account settings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme persistence
        if (localStorage.getItem('sb-theme') === 'dark') document.body.classList.add('dark-mode');
    </script>
</body>
</html>
