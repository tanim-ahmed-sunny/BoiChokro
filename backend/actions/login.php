<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password_hash, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['profile_image'] = $row['profile_image'];

            if ($row['role'] === 'admin') {
                header("Location: ../../admin/dashboard.php");
            } elseif ($row['role'] === 'library') {
                header("Location: ../../library_dashboard.php");
            } else {
                header("Location: ../../index.php");
            }
            exit();
        } else {
            header("Location: ../../login.php?error=Invalid+password");
            exit();
        }
    } else {
        header("Location: ../../login.php?error=No+account+found+with+that+email");
        exit();
    }
} else {
    header("Location: ../../login.php");
    exit();
}
?>
