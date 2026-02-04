<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['library_image']) && isset($_POST['library_id'])) {
    $library_id = (int)$_POST['library_id'];
    $file = $_FILES['library_image'];
    
    // Verify ownership
    $checkQ = $conn->query("SELECT user_id FROM libraries WHERE id = $library_id");
    $libAuth = $checkQ->fetch_assoc();
    
    if (!$libAuth || $libAuth['user_id'] != $user_id) {
        die("Unauthorized access.");
    }
    
    if ($file['error'] === 0) {
        $target_dir = "../uploads/libraries/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = "lib_" . $library_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $image_url = "uploads/libraries/" . $new_filename;
                
                // Update database
                $stmt = $conn->prepare("UPDATE libraries SET image_url = ? WHERE id = ?");
                $stmt->bind_param("si", $image_url, $library_id);
                
                if ($stmt->execute()) {
                    header("Location: ../../library_profile.php?id=$library_id&success=Library image updated successfully!");
                } else {
                    header("Location: ../../library_profile.php?id=$library_id&error=Database update failed.");
                }
                $stmt->close();
            } else {
                header("Location: ../../library_profile.php?id=$library_id&error=Failed to move uploaded file.");
            }
        } else {
            header("Location: ../../library_profile.php?id=$library_id&error=Invalid file type.");
        }
    } else {
        header("Location: ../../library_profile.php?id=$library_id&error=Upload error: " . $file['error']);
    }
} else {
    header("Location: ../../libraries.php");
}
?>
