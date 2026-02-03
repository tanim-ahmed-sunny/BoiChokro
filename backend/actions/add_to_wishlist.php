<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'error' => 'Please login first']);
        exit();
    }
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
    $author = isset($_POST['author']) ? $conn->real_escape_string($_POST['author']) : '';
    $category = isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '';
    
    if (empty($title) && empty($category)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'error' => 'Please provide at least a title or category']);
            exit();
        }
        header("Location: ../../profile.php?error=" . urlencode("Title or Category is required"));
        exit();
    }

    $sql = "INSERT INTO wishlist (user_id, title, author, category) VALUES ($user_id, '$title', '$author', '$category')";
    
    if ($conn->query($sql)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => true, 'message' => 'Added to wishlist!']);
            exit();
        }
        header("Location: ../../profile.php?msg=" . urlencode("Added to wishlist!"));
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => false, 'error' => 'Database error']);
            exit();
        }
        header("Location: ../../profile.php?error=" . urlencode("Error adding to wishlist"));
    }
}
exit();
?>
