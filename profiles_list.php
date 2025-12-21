<?php
include 'db_conn.php';
session_start();

// Optional filters
$q = trim($_GET['q'] ?? '');
$level = trim($_GET['level'] ?? '');
$role = trim($_GET['role'] ?? '');
$sort = trim($_GET['sort'] ?? '');

// Check if ratings table exists for safe fallback
$hasRatings = false;
$tblCheck = $conn->query("SHOW TABLES LIKE 'collaboration_ratings'");
if ($tblCheck && $tblCheck->num_rows === 1) { $hasRatings = true; }

$select = "SELECT u.id, u.full_name, u.role, p.programming_level, p.profile_picture";
if ($hasRatings) {
    $select .= ", ROUND(COALESCE(AVG(cr.coding),0),1) AS avg_coding, ROUND(COALESCE(AVG(cr.report_writing),0),1) AS avg_report, COUNT(cr.id) AS rating_count";
}

$sql = $select . " FROM users u LEFT JOIN profiles p ON u.id = p.user_id";
if ($hasRatings) { $sql .= " LEFT JOIN collaboration_ratings cr ON cr.ratee_id = u.id"; }

$where = [];
$types = '';
$params = [];

if ($q !== '') {
    $where[] = "(u.full_name LIKE ? OR p.skills LIKE ?)";
    $types .= 'ss';
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like;
}
if ($level !== '') {
    $where[] = "p.programming_level = ?";
    $types .= 's';
    $params[] = $level;
}
if ($role !== '') {
    $where[] = "u.role = ?";
    $types .= 's';
    $params[] = $role;
}

if (!empty($where)) { $sql .= " WHERE " . implode(' AND ', $where); }

if ($hasRatings) { $sql .= " GROUP BY u.id"; }

// Sorting
if ($sort === 'best_coding' && $hasRatings) {
    $sql .= " ORDER BY avg_coding DESC, rating_count DESC";
} elseif ($sort === 'best_writing' && $hasRatings) {
    $sql .= " ORDER BY avg_report DESC, rating_count DESC";
} elseif ($sort === 'most_rated' && $hasRatings) {
    $sql .= " ORDER BY rating_count DESC, avg_coding DESC";
} else {
    $sql .= " ORDER BY u.id DESC"; // stable default
}

$sql .= " LIMIT 100";

$stmt = $conn->prepare($sql);
if ($stmt === false) { die('Server error'); }
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
$profiles = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Browse Profiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { overflow-x:hidden; }
        .browse-container { max-width:1200px; margin:0 auto; padding:20px; min-height:100vh; }
        .page-header { text-align:center; margin-bottom:40px; animation:slideDown 0.6s ease; }
        .page-header h2 { font-family:'Poppins',sans-serif; font-weight:700; font-size:2.5rem; background:linear-gradient(135deg,#6f42c1,#5a32a3); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:10px; }
        .page-header p { color:#666; font-size:1.1rem; }
        body.dark-mode .page-header p { color:#aaa; }
        
        .profiles-waterfall { column-count:3; column-gap:24px; }
        @media (max-width:992px){ .profiles-waterfall { column-count:2; } }
        @media (max-width:576px){ .profiles-waterfall { column-count:1; } }
        
        .profile-card { 
            display:inline-block; 
            width:100%; 
            margin:0 0 24px; 
            background:var(--card-light); 
            border-radius:16px; 
            padding:20px; 
            box-shadow:0 10px 40px rgba(111,66,193,0.12); 
            break-inside:avoid; 
            transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
            position:relative;
            overflow:hidden;
            animation:fallIn 0.8s ease forwards;
            opacity:0;
            transform:translateY(-30px) scale(0.9);
        }
        
        body.dark-mode .profile-card { 
            background:var(--card-dark); 
            box-shadow:0 10px 40px rgba(0,0,0,0.4); 
        }
        
        .profile-card::before {
            content:'';
            position:absolute;
            top:0;
            left:-100%;
            width:100%;
            height:100%;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent);
            transition:left 0.5s ease;
        }
        
        .profile-card:hover::before { left:100%; }
        
        .profile-card:hover{ 
            transform:translateY(-8px) scale(1.02); 
            box-shadow:0 20px 60px rgba(111,66,193,0.25); 
        }
        
        body.dark-mode .profile-card:hover { 
            box-shadow:0 20px 60px rgba(111,66,193,0.35); 
        }
        
        @keyframes fallIn {
            to { opacity:1; transform:translateY(0) scale(1); }
        }
        
        .profile-card:nth-child(1) { animation-delay:0.1s; }
        .profile-card:nth-child(2) { animation-delay:0.15s; }
        .profile-card:nth-child(3) { animation-delay:0.2s; }
        .profile-card:nth-child(4) { animation-delay:0.25s; }
        .profile-card:nth-child(5) { animation-delay:0.3s; }
        .profile-card:nth-child(6) { animation-delay:0.35s; }
        .profile-card:nth-child(n+7) { animation-delay:0.4s; }
        
        .profile-avatar { 
            width:80px; 
            height:80px; 
            object-fit:cover; 
            border-radius:16px; 
            box-shadow:0 8px 20px rgba(111,66,193,0.2);
            transition:transform 0.3s ease;
        }
        
        .profile-card:hover .profile-avatar { transform:scale(1.08) rotate(3deg); }
        
        .profile-placeholder { 
            width:80px;
            height:80px;
            border-radius:16px;
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-weight:700;
            font-size:2rem;
            box-shadow:0 8px 20px rgba(111,66,193,0.3);
        }
        
        .profile-info { flex:1; }
        .profile-name { 
            font-weight:700; 
            font-size:1.15rem; 
            margin-bottom:4px;
            color:var(--text-light);
        }
        body.dark-mode .profile-name { color:var(--text-dark); }
        
        .profile-meta { 
            font-size:0.9rem; 
            color:#888;
            display:flex;
            align-items:center;
            gap:8px;
            flex-wrap:wrap;
        }
        body.dark-mode .profile-meta { color:#aaa; }
        
        .badge-skill {
            display:inline-block;
            padding:4px 10px;
            border-radius:20px;
            font-size:0.75rem;
            font-weight:600;
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            color:#fff;
            margin-top:8px;
        }
        
        .profile-row { display:flex; align-items:center; gap:16px; }
        
        .back-top {
            position:fixed;
            bottom:30px;
            right:30px;
            width:50px;
            height:50px;
            background:linear-gradient(135deg,#6f42c1,#5a32a3);
            color:#fff;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow:0 8px 20px rgba(111,66,193,0.4);
            opacity:0;
            transition:all 0.3s ease;
            z-index:999;
        }
        
        .back-top.show { opacity:1; }
        .back-top:hover { transform:translateY(-5px); }
        
        @keyframes slideDown {
            from { opacity:0; transform:translateY(-30px); }
            to { opacity:1; transform:translateY(0); }
        }
        
        .top-controls {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            animation:slideDown 0.5s ease;
        }

        .filters{background:var(--card-light);border-radius:12px;padding:12px;box-shadow:0 8px 24px rgba(0,0,0,0.08);margin-bottom:18px;}
        body.dark-mode .filters{background:var(--card-dark);}        
    </style>
</head>
<body>
    <div class="browse-container">
        <div class="top-controls">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-label="User menu"><i class="fas fa-gear"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="student.php">Dashboard</a></li>
                    <li><a class="dropdown-item" href="edit_profile.php">Edit profile</a></li>
                    <li><a class="dropdown-item" href="account_settings.php">Account settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
                </ul>
            </div>
        </div>
        
        <div class="page-header">
            <h2><i class="fas fa-users"></i> Discover Study Buddies</h2>
            <p>Connect with classmates who match your skills and goals</p>
        </div>

        <form class="filters" method="get" action="profiles_list.php">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search name or skills</label>
                    <input class="form-control" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="e.g. PHP, Java, Databases">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Programming level</label>
                    <select class="form-select" name="level">
                        <option value="">Any</option>
                        <option value="none" <?php echo $level==='none'?'selected':''; ?>>None</option>
                        <option value="beginner" <?php echo $level==='beginner'?'selected':''; ?>>Beginner</option>
                        <option value="intermediate" <?php echo $level==='intermediate'?'selected':''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo $level==='advanced'?'selected':''; ?>>Advanced</option>
                        <option value="expert" <?php echo $level==='expert'?'selected':''; ?>>Expert</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="">Any</option>
                        <option value="student" <?php echo $role==='student'?'selected':''; ?>>Student</option>
                        <option value="lecturer" <?php echo $role==='lecturer'?'selected':''; ?>>Lecturer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort</label>
                    <select class="form-select" name="sort">
                        <option value="">Latest</option>
                        <option value="best_coding" <?php echo $sort==='best_coding'?'selected':''; ?>>Best Coding</option>
                        <option value="best_writing" <?php echo $sort==='best_writing'?'selected':''; ?>>Best Report Writing</option>
                        <option value="most_rated" <?php echo $sort==='most_rated'?'selected':''; ?>>Most Rated</option>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-purple">Apply</button>
                    <a class="btn btn-outline-secondary" href="profiles_list.php">Reset</a>
                </div>
            </div>
        </form>
        
        <div class="profiles-waterfall" id="profilesContainer">
            <?php foreach($profiles as $p): ?>
                <a class="profile-card" href="profile_view.php?id=<?php echo (int)$p['id']; ?>" style="text-decoration:none;">
                    <div class="profile-row">
                        <?php if (!empty($p['profile_picture'])): ?>
                            <img class="profile-avatar" src="uploads/<?php echo htmlspecialchars($p['profile_picture']); ?>" alt="<?php echo htmlspecialchars($p['full_name']); ?>">
                        <?php else: ?>
                            <div class="profile-placeholder"><?php echo strtoupper(substr($p['full_name'],0,1)); ?></div>
                        <?php endif; ?>
                        <div class="profile-info">
                            <div class="profile-name"><?php echo htmlspecialchars($p['full_name']); ?></div>
                            <div class="profile-meta">
                                <span><i class="fas fa-code"></i> <?php echo htmlspecialchars($p['programming_level'] ?? 'None'); ?></span>
                                <span>•</span>
                                <span><i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($p['role'] ?? 'Student'); ?></span>
                            </div>
                            <span class="badge-skill"><?php echo htmlspecialchars($p['programming_level'] ?? 'Beginner'); ?></span>
                            <?php if (isset($p['rating_count'])): ?>
                                <div class="mt-1" style="font-size:0.9rem;">
                                    <span class="badge bg-light text-dark">Coding <?php echo number_format((float)($p['avg_coding'] ?? 0),1); ?>★</span>
                                    <span class="badge bg-light text-dark">Report <?php echo number_format((float)($p['avg_report'] ?? 0),1); ?>★</span>
                                    <span class="badge bg-secondary"><?php echo (int)($p['rating_count'] ?? 0); ?> ratings</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', ()=> {
            const backTop = document.getElementById('backTop');
            if(window.scrollY > 300) {
                backTop.classList.add('show');
            } else {
                backTop.classList.remove('show');
            }
        });
        
        // Theme persistence
        if (localStorage.getItem('sb-theme') === 'dark') document.body.classList.add('dark-mode');
    </script>
</body>
</html>