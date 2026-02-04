<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    
    if ($file['error'] === 0) {
        $target_dir = "../uploads/profiles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $profile_image_path = "uploads/profiles/" . $new_filename;
                
                // Update database
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $profile_image_path, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['profile_image'] = $profile_image_path;
                    header("Location: ../../profile.php?msg=Profile picture updated successfully!");
                } else {
                    header("Location: ../../profile.php?error=Database update failed.");
                }
                $stmt->close();
            } else {
                header("Location: ../../profile.php?error=Failed to move uploaded file.");
            }
        } else {
            header("Location: ../../profile.php?error=Invalid file type. Only JPG, PNG, and WEBP allowed.");
        }
    } else {
        header("Location: ../../profile.php?error=File upload error code: " . $file['error']);
    }
} else {
    header("Location: ../../profile.php");
}
?>
