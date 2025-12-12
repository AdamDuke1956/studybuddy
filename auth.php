<?php
session_start();
include "db_conn.php";

// HANDLE SIGN UP
if (isset($_POST['signup'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing

    $sql = "INSERT INTO users (full_name, email, password) VALUES ('$name', '$email', '$pass')";
    if (mysqli_query($conn, $sql)) {
        header("Location: login.php?success=Account created successfully");
    } else {
        header("Location: signup.php?error=unknown error");
    }
}

// HANDLE LOG IN
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            header("Location: index.php");
        } else {
            header("Location: login.php?error=Incorrect password");
        }
    } else {
        header("Location: login.php?error=User not found");
    }
}
?>