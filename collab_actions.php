<?php
session_start();
require_once 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php?error=' . urlencode('Please log in first'));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function isCollabMember(mysqli $conn, int $collabId, int $userId): bool {
    $stmt = $conn->prepare('SELECT 1 FROM collaboration_members WHERE collaboration_id = ? AND user_id = ? LIMIT 1');
    if (!$stmt) { return false; }
    $stmt->bind_param('ii', $collabId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows === 1;
    $stmt->close();
    return $ok;
}

if ($action === 'send_request') {
    $targetId = isset($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : 0;
    $assignmentTitle = trim($_POST['assignment_title'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $subjects = trim($_POST['subjects'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($targetId <= 0 || $targetId === $userId || $assignmentTitle === '') {
        header('Location: profile_view.php?id=' . $targetId . '&error=' . urlencode('Please provide assignment title and choose a different user'));
        exit;
    }

    // Avoid duplicate pending for same pair + title
    $dupe = $conn->prepare('SELECT id FROM collab_requests WHERE requester_id = ? AND target_user_id = ? AND assignment_title = ? AND status = "pending" LIMIT 1');
    if ($dupe) {
        $dupe->bind_param('iis', $userId, $targetId, $assignmentTitle);
        $dupe->execute();
        $res = $dupe->get_result();
        if ($res && $res->num_rows > 0) {
            header('Location: profile_view.php?id=' . $targetId . '&error=' . urlencode('You already sent a pending request for this assignment'));
            exit;
        }
        $dupe->close();
    }

    $stmt = $conn->prepare('INSERT INTO collab_requests (requester_id, target_user_id, assignment_title, course, subjects, description) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        header('Location: profile_view.php?id=' . $targetId . '&error=' . urlencode('Server error'));
        exit;
    }
    $stmt->bind_param('iissss', $userId, $targetId, $assignmentTitle, $course, $subjects, $description);
    $stmt->execute();
    $stmt->close();
    header('Location: profile_view.php?id=' . $targetId . '&success=' . urlencode('Collaboration request sent'));
    exit;
}

if ($action === 'respond_request') {
    $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $decision = $_POST['decision'] ?? '';
    if ($requestId <= 0 || !in_array($decision, ['accepted','declined'], true)) {
        header('Location: collab_requests.php?error=' . urlencode('Invalid request'));
        exit;
    }

    $stmt = $conn->prepare('SELECT * FROM collab_requests WHERE id = ? AND target_user_id = ? AND status = "pending" LIMIT 1');
    if (!$stmt) { header('Location: collab_requests.php?error=' . urlencode('Server error')); exit; }
    $stmt->bind_param('ii', $requestId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) {
        header('Location: collab_requests.php?error=' . urlencode('Request not found or already handled'));
        exit;
    }
    $req = $res->fetch_assoc();
    $stmt->close();

    $upd = $conn->prepare('UPDATE collab_requests SET status = ? WHERE id = ?');
    $upd->bind_param('si', $decision, $requestId);
    $upd->execute();
    $upd->close();

    if ($decision === 'accepted') {
        // Create collaboration and members
        $collab = $conn->prepare('INSERT INTO collaborations (assignment_title, course, subjects, created_by, request_id) VALUES (?, ?, ?, ?, ?)');
        if ($collab) {
            $collab->bind_param('sssii', $req['assignment_title'], $req['course'], $req['subjects'], $req['requester_id'], $requestId);
            $collab->execute();
            $collabId = $collab->insert_id;
            $collab->close();

            $member = $conn->prepare('INSERT IGNORE INTO collaboration_members (collaboration_id, user_id, role) VALUES (?, ?, "member")');
            if ($member) {
                $member->bind_param('ii', $collabId, $req['requester_id']);
                $member->execute();
                $member->bind_param('ii', $collabId, $userId);
                $member->execute();
                $member->close();
            }
        }
        header('Location: collaborations.php?success=' . urlencode('Request accepted. Collaboration created.') . '&collab=' . $collabId);
        exit;
    }

    header('Location: collab_requests.php?success=' . urlencode('Request declined'));
    exit;
}

if ($action === 'add_task') {
    $collabId = (int)($_POST['collab_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($collabId <= 0 || $title === '' || !isCollabMember($conn, $collabId, $userId)) {
        header('Location: collaborations.php?error=' . urlencode('Cannot add task'));
        exit;
    }
    $stmt = $conn->prepare('INSERT INTO collaboration_tasks (collaboration_id, title) VALUES (?, ?)');
    $stmt->bind_param('is', $collabId, $title);
    $stmt->execute();
    $stmt->close();
    header('Location: collaborations.php?collab=' . $collabId . '&success=' . urlencode('Task added'));
    exit;
}

if ($action === 'toggle_task') {
    $collabId = (int)($_POST['collab_id'] ?? 0);
    $taskId = (int)($_POST['task_id'] ?? 0);
    if ($collabId <= 0 || $taskId <= 0 || !isCollabMember($conn, $collabId, $userId)) {
        header('Location: collaborations.php?error=' . urlencode('Cannot update task'));
        exit;
    }
    $stmt = $conn->prepare('SELECT status FROM collaboration_tasks WHERE id = ? AND collaboration_id = ? LIMIT 1');
    $stmt->bind_param('ii', $taskId, $collabId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$row) {
        header('Location: collaborations.php?error=' . urlencode('Task not found'));
        exit;
    }
    $newStatus = $row['status'] === 'done' ? 'todo' : 'done';
    $upd = $conn->prepare('UPDATE collaboration_tasks SET status = ? WHERE id = ?');
    $upd->bind_param('si', $newStatus, $taskId);
    $upd->execute();
    $upd->close();
    header('Location: collaborations.php?collab=' . $collabId . '&success=' . urlencode('Task updated'));
    exit;
}

if ($action === 'send_message') {
    $collabId = (int)($_POST['collab_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');
    if ($collabId <= 0 || $body === '' || !isCollabMember($conn, $collabId, $userId)) {
        header('Location: collab_chat.php?collab_id=' . $collabId . '&error=' . urlencode('Cannot send message'));
        exit;
    }
    $stmt = $conn->prepare('INSERT INTO collaboration_messages (collaboration_id, sender_id, body) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $collabId, $userId, $body);
    $stmt->execute();
    $stmt->close();
    header('Location: collab_chat.php?collab_id=' . $collabId . '&success=' . urlencode('Message sent'));
    exit;
}

if ($action === 'rate') {
    $collabId = (int)($_POST['collab_id'] ?? 0);
    $rateeId = (int)($_POST['ratee_id'] ?? 0);
    $report = max(1, min(5, (int)($_POST['report_writing'] ?? 0)));
    $coding = max(1, min(5, (int)($_POST['coding'] ?? 0)));
    $comment = trim($_POST['comment'] ?? '');
    if ($collabId <= 0 || $rateeId <= 0 || $rateeId === $userId || !isCollabMember($conn, $collabId, $userId) || !isCollabMember($conn, $collabId, $rateeId)) {
        header('Location: collaborations.php?collab=' . $collabId . '&error=' . urlencode('Cannot rate'));
        exit;
    }
    // Allow rating only when collaboration is completed
    $statusStmt = $conn->prepare('SELECT status FROM collaborations WHERE id = ? LIMIT 1');
    $statusStmt->bind_param('i', $collabId);
    $statusStmt->execute();
    $statusRes = $statusStmt->get_result();
    $statusRow = $statusRes ? $statusRes->fetch_assoc() : null;
    $statusStmt->close();
    if (!$statusRow || $statusRow['status'] !== 'completed') {
        header('Location: collaborations.php?collab=' . $collabId . '&error=' . urlencode('You can rate after marking collaboration completed'));
        exit;
    }

    $stmt = $conn->prepare('REPLACE INTO collaboration_ratings (collaboration_id, rater_id, ratee_id, report_writing, coding, comment) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiiiis', $collabId, $userId, $rateeId, $report, $coding, $comment);
    $stmt->execute();
    $stmt->close();
    header('Location: collaborations.php?collab=' . $collabId . '&success=' . urlencode('Rating saved'));
    exit;
}

if ($action === 'mark_collab_status') {
    $collabId = (int)($_POST['collab_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if ($collabId <= 0 || !in_array($newStatus, ['active','completed','cancelled'], true)) {
        header('Location: collaborations.php?error=' . urlencode('Invalid status'));
        exit;
    }
    // Only creator can change status
    $stmt = $conn->prepare('SELECT created_by FROM collaborations WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $collabId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$row || (int)$row['created_by'] !== $userId) {
        header('Location: collaborations.php?collab=' . $collabId . '&error=' . urlencode('Only the creator can update status'));
        exit;
    }
    $upd = $conn->prepare('UPDATE collaborations SET status = ?, completed_at = CASE WHEN ? = "completed" THEN NOW() ELSE completed_at END WHERE id = ?');
    $upd->bind_param('ssi', $newStatus, $newStatus, $collabId);
    $upd->execute();
    $upd->close();
    header('Location: collaborations.php?collab=' . $collabId . '&success=' . urlencode('Collaboration status updated'));
    exit;
}

header('Location: index.php');
exit;
