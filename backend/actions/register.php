<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        header("Location: ../../register.php?error=Passwords+do+not+match");
        exit();
    } else {
        // Check if email or username exists
        $check = "SELECT id FROM users WHERE email = '$email' OR username = '$username'";
        if ($conn->query($check)->num_rows > 0) {
            header("Location: ../../register.php?error=Email+or+Username+already+registered");
            exit();
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, username, email, password_hash) VALUES ('$full_name', '$username', '$email', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                header("Location: ../../login.php?success=Registration+successful!+You+can+now+login");
                exit();
            } else {
                header("Location: ../../register.php?error=" . urlencode("Error: " . $conn->error));
                exit();
            }
        }
    }
} else {
    header("Location: ../../register.php");
    exit();
}
?>
