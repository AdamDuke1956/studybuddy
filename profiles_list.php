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
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profiles-grid { max-width:900px; margin:0 auto; column-count:3; column-gap:18px; }
        @media (max-width:900px){ .profiles-grid { column-count:2; } }
        @media (max-width:520px){ .profiles-grid { column-count:1; } }
        .profile-card { display:inline-block; width:100%; margin:0 0 18px; background:var(--card-light); border-radius:12px; padding:14px; box-shadow:0 8px 30px rgba(0,0,0,0.06); break-inside:avoid; transition:transform .35s ease, box-shadow .35s ease; }
        body.dark-mode .profile-card { background:var(--card-dark); box-shadow:0 8px 30px rgba(0,0,0,0.35); }
        .profile-card:hover{ transform:translateY(-6px); box-shadow:0 18px 60px rgba(0,0,0,0.08); }
        .profile-card img{ width:64px; height:64px; object-fit:cover; border-radius:10px; margin-right:12px; }
        .profile-row{ display:flex; align-items:center; gap:10px; }
        .slow-scroll{ max-height:70vh; overflow:auto; padding:20px 0; scroll-behavior:smooth; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div style="width:100%; max-width:960px;">
            <h2 style="text-align:center; margin-bottom:18px;">Browse members</h2>
            <div class="slow-scroll">
                <div class="profiles-grid">
                    <?php foreach($profiles as $p): ?>
                        <a class="profile-card" href="profile_view.php?id=<?php echo (int)$p['id']; ?>" style="text-decoration:none;color:inherit;">
                            <div class="profile-row">
                                <?php if (!empty($p['profile_picture'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($p['profile_picture']); ?>" alt="<?php echo htmlspecialchars($p['full_name']); ?>">
                                <?php else: ?>
                                    <div style="width:64px;height:64px;border-radius:10px;background:linear-gradient(135deg,#f0f0f0,#e8e8e8);display:flex;align-items:center;justify-content:center;color:#888;font-weight:700;"><?php echo strtoupper(substr($p['full_name'],0,1)); ?></div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:700; font-size:1rem"><?php echo htmlspecialchars($p['full_name']); ?></div>
                                    <div style="font-size:0.85rem; color: #666;"><?php echo htmlspecialchars($p['programming_level'] ?? '—'); ?> • <?php echo htmlspecialchars($p['role'] ?? ''); ?></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>