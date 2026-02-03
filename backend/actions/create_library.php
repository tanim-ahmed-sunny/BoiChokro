<?php
require_once '../includes/db.php';
session_start();

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$userQ = $conn->query("SELECT role FROM users WHERE id=$user_id");
$user = $userQ->fetch_assoc();

if ($user['role'] !== 'admin') {
    die("Access Denied. Admins only.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $full_name = $conn->real_escape_string($_POST['library_name']); // User full name = Lib Name
    
    $library_name = $conn->real_escape_string($_POST['library_name']);
    $library_type = $conn->real_escape_string($_POST['library_type']);
    $address = $conn->real_escape_string($_POST['address']);
    $city = $conn->real_escape_string($_POST['city']);
    $area = $conn->real_escape_string($_POST['area']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Check if email or username exists
    $check = "SELECT id FROM users WHERE email = '$email' OR username = '$username'";
    if ($conn->query($check)->num_rows > 0) {
        header("Location: ../../admin/dashboard.php?error=" . urlencode("Email or Username already registered"));
        exit();
    }

    // Handle Image Upload
    $image_url = 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&q=80&w=800'; // Default
    if (isset($_FILES['library_image']) && $_FILES['library_image']['error'] == 0) {
        $target_dir = "../uploads/libraries/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES["library_image"]["name"], PATHINFO_EXTENSION);
        $file_name = "lib_" . time() . "." . $file_ext;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["library_image"]["tmp_name"], $target_file)) {
            $image_url = "uploads/libraries/" . $file_name;
        }
    }

    // 1. Create User
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $userSql = "INSERT INTO users (username, email, password_hash, full_name, role, email_verified, status) 
                VALUES ('$username', '$email', '$hashed_password', '$full_name', 'library', 1, 'active')";
    
    if ($conn->query($userSql) === TRUE) {
        $new_user_id = $conn->insert_id;
        
        // 2. Create Library Entry
        $libSql = "INSERT INTO libraries (user_id, library_name, library_type, address, city, area, contact_phone, contact_email, status, image_url) 
                   VALUES ($new_user_id, '$library_name', '$library_type', '$address', '$city', '$area', '$phone', '$email', 'approved', '$image_url')";
                   
        if ($conn->query($libSql) === TRUE) {
             header("Location: ../../admin/dashboard.php?msg=" . urlencode("Library account created successfully!"));
        } else {
             // Rollback user creation if library fails (optional but good practice, here manual cleanup ideally)
             $conn->query("DELETE FROM users WHERE id=$new_user_id");
             header("Location: ../../admin/dashboard.php?error=" . urlencode("Error creating library profile: " . $conn->error));
        }
    } else {
        header("Location: ../../admin/dashboard.php?error=" . urlencode("Error creating user: " . $conn->error));
    }
} else {
    header("Location: ../../admin/dashboard.php");
}
?>
