<?php
session_start();
include "db_conn.php";

// HANDLE SIGN UP
if (isset($_POST['signup'])) {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = isset($_POST['role']) && $_POST['role'] === 'lecturer' ? 'lecturer' : 'student';
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        header("Location: signuppage.php?error=" . urlencode("Please fill all fields"));
        exit;
    }

    $pass_hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $pass_hashed, $role);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: loginpage.php?success=" . urlencode("Account created successfully"));
            exit;
        } else {
            $stmt->close();
            header("Location: signuppage.php?error=" . urlencode("Could not create account"));
            exit;
        }
    } else {
        header("Location: signuppage.php?error=" . urlencode("Server error"));
        exit;
    }
}

// HANDLE LOG IN
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: loginpage.php?error=" . urlencode("Please provide email and password"));
        exit;
    }

    $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['full_name'];
                $_SESSION['user_role'] = $row['role'];

                // if profile doesn't exist yet, force profile creation first
                $profileStmt = $conn->prepare("SELECT user_id FROM profiles WHERE user_id = ? LIMIT 1");
                if ($profileStmt) {
                    $profileStmt->bind_param('i', $row['id']);
                    $profileStmt->execute();
                    $profileRes = $profileStmt->get_result();
                    $hasProfile = $profileRes && $profileRes->num_rows === 1;
                    $profileStmt->close();
                } else {
                    $hasProfile = false;
                }

                if (!$hasProfile) {
                    header("Location: create_profile.php");
                    exit;
                }

                if ($row['role'] === 'lecturer') {
                    header("Location: lecturer.php");
                    exit;
                } else {
                    header("Location: student.php");
                    exit;
                }
            } else {
                header("Location: loginpage.php?error=" . urlencode("Incorrect password"));
                exit;
            }
        } else {
            header("Location: loginpage.php?error=" . urlencode("User not found"));
            exit;
        }
    } else {
        header("Location: loginpage.php?error=" . urlencode("Server error"));
        exit;
    }
}

// HANDLE EMAIL CHANGE
if (isset($_POST['change_email'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: loginpage.php?error=' . urlencode('Please log in first'));
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $new_email = trim($_POST['new_email']);
    $current_password = $_POST['current_password'] ?? '';
    if (empty($new_email) || empty($current_password)) {
        header('Location: account_settings.php?error=' . urlencode('Provide email and password'));
        exit;
    }
    $stmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) { header('Location: account_settings.php?error=' . urlencode('User not found')); exit; }
    $row = $res->fetch_assoc();
    if (!password_verify($current_password, $row['password'])) {
        header('Location: account_settings.php?error=' . urlencode('Incorrect password'));
        exit;
    }
    $u = $conn->prepare('UPDATE users SET email = ? WHERE id = ?');
    $u->bind_param('si', $new_email, $user_id);
    if ($u->execute()) {
        $u->close();
        header('Location: account_settings.php?success=' . urlencode('Email updated'));
        exit;
    } else {
        header('Location: account_settings.php?error=' . urlencode('Could not update email'));
        exit;
    }
}

// HANDLE PASSWORD CHANGE
if (isset($_POST['change_password'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: loginpage.php?error=' . urlencode('Please log in first'));
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'] ?? '';
    $newpass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (empty($current) || empty($newpass) || empty($confirm)) {
        header('Location: account_settings.php?error=' . urlencode('Fill all password fields'));
        exit;
    }
    if ($newpass !== $confirm) {
        header('Location: account_settings.php?error=' . urlencode('Passwords do not match'));
        exit;
    }
    $stmt = $conn->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) { header('Location: account_settings.php?error=' . urlencode('User not found')); exit; }
    $row = $res->fetch_assoc();
    if (!password_verify($current, $row['password'])) {
        header('Location: account_settings.php?error=' . urlencode('Incorrect current password'));
        exit;
    }
    $hash = password_hash($newpass, PASSWORD_DEFAULT);
    $u = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    $u->bind_param('si', $hash, $user_id);
    if ($u->execute()) {
        $u->close();
        header('Location: account_settings.php?success=' . urlencode('Password changed'));
        exit;
    } else {
        header('Location: account_settings.php?error=' . urlencode('Could not update password'));
        exit;
    }
}
?>