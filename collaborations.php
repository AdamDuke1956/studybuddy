<?php
session_start();
require 'db_conn.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}
$userId = (int)$_SESSION['user_id'];
$selectedCollab = isset($_GET['collab']) ? (int)$_GET['collab'] : 0;

// All collaborations for sidebar/list
$listStmt = $conn->prepare('SELECT c.* FROM collaborations c JOIN collaboration_members m ON m.collaboration_id = c.id WHERE m.user_id = ? ORDER BY c.created_at DESC');
$listStmt->bind_param('i', $userId);
$listStmt->execute();
$collabs = $listStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

$currentCollab = null;
$members = [];
$tasks = [];
$ratingsByMe = [];
$myIsCreator = false;

if ($selectedCollab > 0) {
    // Ensure membership
    $cstmt = $conn->prepare('SELECT * FROM collaborations WHERE id = ? AND id IN (SELECT collaboration_id FROM collaboration_members WHERE user_id = ?) LIMIT 1');
    $cstmt->bind_param('ii', $selectedCollab, $userId);
    $cstmt->execute();
    $cres = $cstmt->get_result();
    $currentCollab = $cres ? $cres->fetch_assoc() : null;
    $cstmt->close();
    if ($currentCollab) {
        $myIsCreator = ((int)$currentCollab['created_by'] === $userId);
        $mstmt = $conn->prepare('SELECT u.id, u.full_name FROM collaboration_members cm JOIN users u ON cm.user_id = u.id WHERE cm.collaboration_id = ?');
        $mstmt->bind_param('i', $selectedCollab);
        $mstmt->execute();
        $members = $mstmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $mstmt->close();

        $tstmt = $conn->prepare('SELECT * FROM collaboration_tasks WHERE collaboration_id = ? ORDER BY created_at ASC');
        $tstmt->bind_param('i', $selectedCollab);
        $tstmt->execute();
        $tasks = $tstmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $tstmt->close();

        $rstmt = $conn->prepare('SELECT * FROM collaboration_ratings WHERE collaboration_id = ? AND rater_id = ?');
        $rstmt->bind_param('ii', $selectedCollab, $userId);
        $rstmt->execute();
        $ratingsByMe = $rstmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $rstmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collaborations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .collab-list{max-height:520px;overflow-y:auto;}
        .collab-item{padding:10px 12px;border-radius:12px;margin-bottom:10px;border:1px solid rgba(0,0,0,0.05);cursor:pointer;text-decoration:none;display:block;color:inherit;background:var(--card-light);}
        body.dark-mode .collab-item{background:var(--card-dark);}
        .collab-item.active{border-color:var(--primary-color);box-shadow:0 10px 25px rgba(111,66,193,0.15);}        
        .task-pill{border-radius:999px;padding:4px 10px;font-size:0.8rem;font-weight:700;}
        .task-todo{background:rgba(111,66,193,0.15);color:#5a32a3;}
        .task-done{background:rgba(25,135,84,0.2);color:#198754;}
    </style>
</head>
<body>
<div class="main-wrapper" style="align-items:flex-start;">
    <div class="sb-card" style="max-width:1100px;width:100%;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 style="margin-bottom:0;">Collaborations</h3>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="student.php">Dashboard</a>
                <a class="btn btn-outline-secondary btn-sm" href="collab_requests.php">Requests</a>
            </div>
        </div>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div><?php endif; ?>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="collab-list">
                    <?php if (empty($collabs)): ?>
                        <p class="text-muted">No collaborations yet. Accept a request to start.</p>
                    <?php else: foreach($collabs as $c): ?>
                        <a class="collab-item <?php echo $selectedCollab === (int)$c['id'] ? 'active' : ''; ?>" href="collaborations.php?collab=<?php echo (int)$c['id']; ?>">
                            <div class="fw-bold"><?php echo htmlspecialchars($c['assignment_title']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($c['course']); ?> • <?php echo htmlspecialchars($c['subjects']); ?></div>
                            <div class="small">Status: <?php echo htmlspecialchars($c['status']); ?></div>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="col-md-8">
                <?php if (!$currentCollab): ?>
                    <p class="text-muted">Select a collaboration to manage tasks, chat, and ratings.</p>
                <?php else: ?>
                    <div class="mb-3">
                        <h4 class="mb-1"><?php echo htmlspecialchars($currentCollab['assignment_title']); ?></h4>
                        <div class="text-muted">Course: <?php echo htmlspecialchars($currentCollab['course']); ?> • <?php echo htmlspecialchars($currentCollab['subjects']); ?></div>
                        <div class="mt-2">Status: <span class="badge bg-secondary"><?php echo htmlspecialchars($currentCollab['status']); ?></span>
                            <?php if ($myIsCreator): ?>
                                <form action="collab_actions.php" method="post" class="d-inline ms-2">
                                    <input type="hidden" name="action" value="mark_collab_status">
                                    <input type="hidden" name="collab_id" value="<?php echo (int)$selectedCollab; ?>">
                                    <select name="status" class="form-select form-select-sm d-inline w-auto">
                                        <option value="active" <?php echo $currentCollab['status']==='active'?'selected':''; ?>>active</option>
                                        <option value="completed" <?php echo $currentCollab['status']==='completed'?'selected':''; ?>>completed</option>
                                        <option value="cancelled" <?php echo $currentCollab['status']==='cancelled'?'selected':''; ?>>cancelled</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">Members:
                            <?php foreach($members as $m): ?>
                                <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($m['full_name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="collab_chat.php?collab_id=<?php echo (int)$selectedCollab; ?>">Open chat</a>
                            <a class="btn btn-outline-secondary btn-sm" href="collab_requests.php">Requests</a>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Progress tracker</h5>
                        <form action="collab_actions.php" method="post" class="d-flex gap-2 mb-2">
                            <input type="hidden" name="action" value="add_task">
                            <input type="hidden" name="collab_id" value="<?php echo (int)$selectedCollab; ?>">
                            <input class="form-control" name="title" placeholder="Add task (e.g., Research, PHP API)" required>
                            <button class="btn btn-purple">Add</button>
                        </form>
                        <?php if (empty($tasks)): ?>
                            <p class="text-muted">No tasks yet.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach($tasks as $t): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="task-pill task-<?php echo $t['status']; ?>"><?php echo strtoupper($t['status']); ?></span>
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </div>
                                        <form action="collab_actions.php" method="post" class="m-0">
                                            <input type="hidden" name="action" value="toggle_task">
                                            <input type="hidden" name="collab_id" value="<?php echo (int)$selectedCollab; ?>">
                                            <input type="hidden" name="task_id" value="<?php echo (int)$t['id']; ?>">
                                            <button class="btn btn-sm btn-outline-primary">Toggle</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h5>Rate collaborators (after completion)</h5>
                        <?php if ($currentCollab['status'] !== 'completed'): ?>
                            <p class="text-muted">Mark collaboration as completed to enable ratings.</p>
                        <?php else: ?>
                            <?php foreach($members as $m): if ((int)$m['id'] === $userId) continue; ?>
                                <?php
                                    $existing = null;
                                    foreach($ratingsByMe as $r){ if ((int)$r['ratee_id'] === (int)$m['id']) { $existing = $r; break; }}
                                ?>
                                <form action="collab_actions.php" method="post" class="border rounded p-2 mb-2">
                                    <input type="hidden" name="action" value="rate">
                                    <input type="hidden" name="collab_id" value="<?php echo (int)$selectedCollab; ?>">
                                    <input type="hidden" name="ratee_id" value="<?php echo (int)$m['id']; ?>">
                                    <div class="fw-bold mb-1">Rate <?php echo htmlspecialchars($m['full_name']); ?></div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label small mb-0">Report Writing</label>
                                            <input type="number" min="1" max="5" class="form-control" name="report_writing" value="<?php echo $existing ? (int)$existing['report_writing'] : 3; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small mb-0">Coding Knowledge</label>
                                            <input type="number" min="1" max="5" class="form-control" name="coding" value="<?php echo $existing ? (int)$existing['coding'] : 3; ?>" required>
                                        </div>
                                    </div>
                                    <textarea class="form-control mb-2" name="comment" rows="2" placeholder="Feedback (optional)"><?php echo $existing ? htmlspecialchars($existing['comment']) : ''; ?></textarea>
                                    <button class="btn btn-sm btn-purple">Save rating</button>
                                    <?php if ($existing): ?><span class="text-success ms-2">Saved</span><?php endif; ?>
                                </form>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
