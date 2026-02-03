<?php
require_once '../includes/db.php';
session_start();

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['library_id'])) {
    $library_id = (int)$_POST['library_id'];
    $library_name = $conn->real_escape_string($_POST['library_name']);
    $library_type = $conn->real_escape_string($_POST['library_type']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $city = $conn->real_escape_string($_POST['city']);
    $area = $conn->real_escape_string($_POST['area']);
    $address = $conn->real_escape_string($_POST['address']);

    $imageUpdate = "";
    if (isset($_FILES['library_image']) && $_FILES['library_image']['error'] == 0) {
        $target_dir = "../uploads/libraries/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES["library_image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_name = "lib_" . $library_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_name;
            
            if (move_uploaded_file($_FILES["library_image"]["tmp_name"], $target_file)) {
                $image_url = "uploads/libraries/" . $new_name;
                $imageUpdate = ", image_url='$image_url'";
            }
        }
    }

    $sql = "UPDATE libraries SET 
            library_name='$library_name', 
            library_type='$library_type', 
            contact_phone='$phone', 
            city='$city', 
            area='$area', 
            address='$address' 
            $imageUpdate 
            WHERE id=$library_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../../admin/dashboard.php?tab=libraries&msg=" . urlencode("Library details updated successfully!"));
    } else {
        header("Location: ../../admin/dashboard.php?tab=libraries&error=" . urlencode("Error updating library: " . $conn->error));
    }
} else {
    header("Location: ../../admin/dashboard.php");
}
exit();
?>
