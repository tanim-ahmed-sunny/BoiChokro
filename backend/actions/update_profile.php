<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $city = $conn->real_escape_string($_POST['city']);
    $area = $conn->real_escape_string($_POST['area']);
    
    $imageUpdate = "";
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../uploads/profiles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_name;
            
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_path = "uploads/profiles/" . $new_name;
                $imageUpdate = ", profile_image='$profile_path'";
                $_SESSION['profile_image'] = $profile_path;
            }
        }
    }
    
    $updateSql = "UPDATE users SET full_name='$full_name', phone='$phone', address='$address', city='$city', area='$area' $imageUpdate WHERE id=$user_id";
    if ($conn->query($updateSql) === TRUE) {
        header("Location: ../../profile.php?msg=" . urlencode("Profile updated successfully!"));
    } else {
        header("Location: ../../profile.php?error=" . urlencode("Error updating profile: " . $conn->error));
    }
} else {
    header("Location: ../../profile.php");
}
exit();
?>
