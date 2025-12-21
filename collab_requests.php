<?php
session_start();
require 'db_conn.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}
$userId = (int)$_SESSION['user_id'];

// incoming pending
$inStmt = $conn->prepare('SELECT cr.*, u.full_name AS requester_name FROM collab_requests cr JOIN users u ON cr.requester_id = u.id WHERE cr.target_user_id = ? ORDER BY cr.created_at DESC');
$inStmt->bind_param('i', $userId);
$inStmt->execute();
$incoming = $inStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inStmt->close();

$outStmt = $conn->prepare('SELECT cr.*, u.full_name AS target_name FROM collab_requests cr JOIN users u ON cr.target_user_id = u.id WHERE cr.requester_id = ? ORDER BY cr.created_at DESC');
$outStmt->bind_param('i', $userId);
$outStmt->execute();
$outgoing = $outStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$outStmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collaboration Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .req-card{background:var(--card-light);border-radius:14px;padding:16px;box-shadow:0 10px 30px rgba(0,0,0,0.08);margin-bottom:12px;}
        body.dark-mode .req-card{background:var(--card-dark);}
        .status-pill{padding:4px 10px;border-radius:999px;font-size:0.8rem;font-weight:700;text-transform:uppercase;}
        .status-pending{background:rgba(255,193,7,0.2);color:#b58100;}
        .status-accepted{background:rgba(25,135,84,0.2);color:#198754;}
        .status-declined{background:rgba(220,53,69,0.2);color:#c1121f;}
    </style>
</head>
<body>
    <div class="main-wrapper" style="align-items:flex-start;">
        <div class="sb-card" style="max-width:900px;width:100%;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 style="margin-bottom:0;">Collaboration Requests</h3>
                <div class="d-flex gap-2">
                    <a href="student.php" class="btn btn-outline-secondary btn-sm">Dashboard</a>
                    <a href="collaborations.php" class="btn btn-purple btn-sm">Collaborations</a>
                </div>
            </div>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
            <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div><?php endif; ?>

            <h5 class="mt-3">Incoming</h5>
            <?php if (empty($incoming)): ?>
                <p class="text-muted">No incoming requests.</p>
            <?php else: foreach($incoming as $req): ?>
                <div class="req-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($req['assignment_title']); ?></div>
                            <div class="small text-muted">From <?php echo htmlspecialchars($req['requester_name']); ?> • <?php echo htmlspecialchars($req['course']); ?> • <?php echo htmlspecialchars($req['subjects']); ?></div>
                            <div class="mt-2"><?php echo nl2br(htmlspecialchars($req['description'])); ?></div>
                        </div>
                        <div class="text-end">
                            <span class="status-pill status-<?php echo htmlspecialchars($req['status']); ?>"><?php echo strtoupper($req['status']); ?></span><br>
                            <?php if ($req['status'] === 'pending'): ?>
                                <form action="collab_actions.php" method="post" class="mt-2 d-flex gap-2">
                                    <input type="hidden" name="action" value="respond_request">
                                    <input type="hidden" name="request_id" value="<?php echo (int)$req['id']; ?>">
                                    <button class="btn btn-success btn-sm" name="decision" value="accepted">Accept</button>
                                    <button class="btn btn-outline-danger btn-sm" name="decision" value="declined">Decline</button>
                                </form>
                            <?php else: ?>
                                <small class="text-muted">Handled</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>

            <h5 class="mt-4">Outgoing</h5>
            <?php if (empty($outgoing)): ?>
                <p class="text-muted">You have not sent any requests.</p>
            <?php else: foreach($outgoing as $req): ?>
                <div class="req-card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($req['assignment_title']); ?></div>
                            <div class="small text-muted">To <?php echo htmlspecialchars($req['target_name']); ?> • <?php echo htmlspecialchars($req['course']); ?> • <?php echo htmlspecialchars($req['subjects']); ?></div>
                            <div class="mt-2"><?php echo nl2br(htmlspecialchars($req['description'])); ?></div>
                        </div>
                        <div class="text-end">
                            <span class="status-pill status-<?php echo htmlspecialchars($req['status']); ?>"><?php echo strtoupper($req['status']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</body>
</html>
