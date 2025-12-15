<?php
include 'db_conn.php';
session_start();

// Fetch random users with profiles
$stmt = $conn->prepare("SELECT u.id, u.full_name, u.role, p.programming_level, p.profile_picture FROM users u LEFT JOIN profiles p ON u.id = p.user_id ORDER BY RAND() LIMIT 100");
$stmt->execute();
$res = $stmt->get_result();
$profiles = $res->fetch_all(MYSQLI_ASSOC);
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