<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $category = $conn->real_escape_string($_POST['category']);
    $condition = $conn->real_escape_string($_POST['condition']);
    $listing_type = $conn->real_escape_string($_POST['listing_type']);
    $price = (float)$_POST['price'];
    $swap_interest = isset($_POST['swap_interest']) ? $conn->real_escape_string($_POST['swap_interest']) : '';
    $city = $conn->real_escape_string($_POST['city']);
    $area = $conn->real_escape_string($_POST['area']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // Handle File Upload
    $image_url = ''; // Default or placeholder if needed
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/books/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        
        // Get extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensions = array("jpeg", "jpg", "png", "webp");
        
        if (in_array($file_ext, $extensions)) {
            if ($file_size < 5000000) { // 5MB
                $new_file_name = uniqid() . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $image_url = 'backend/uploads/books/' . $new_file_name;
                } else {
                    header("Location: ../../add_book.php?error=" . urlencode("Failed to save uploaded image."));
                    exit();
                }
            } else {
                header("Location: ../../add_book.php?error=" . urlencode("File size too large (Max 5MB)."));
                exit();
            }
        } else {
            header("Location: ../../add_book.php?error=" . urlencode("Invalid file type. Only JPG, JPEG, PNG, WEBP allowed."));
            exit();
        }
    }

    $sql = "INSERT INTO books (user_id, title, author, isbn, category, `condition`, listing_type, price, swap_interest, city, area, description, images, status) 
            VALUES ($user_id, '$title', '$author', '$isbn', '$category', '$condition', '$listing_type', $price, '$swap_interest', '$city', '$area', '$description', '$image_url', 'approved')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../../add_book.php?msg=" . urlencode("Book posted successfully! It is now live."));
    } else {
        header("Location: ../../add_book.php?error=" . urlencode("Error posting book: " . $conn->error));
    }
} else {
    header("Location: ../../add_book.php");
}
exit();
?>
