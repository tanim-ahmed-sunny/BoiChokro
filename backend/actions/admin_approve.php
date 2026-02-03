<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Check role
$userQ = $conn->query("SELECT role FROM users WHERE id=$user_id");
$user = $userQ->fetch_assoc();

if ($user['role'] !== 'admin') {
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    $status = $_POST['action'] == 'approve' ? 'approved' : 'rejected';
    
    if($conn->query("UPDATE books SET status='$status', approved_by=$user_id, approved_at=NOW() WHERE id=$book_id")) {
        
        // Wishlist Notification Logic (Only if approved)
        if ($status === 'approved') {
            $bookRes = $conn->query("SELECT title, author, category FROM books WHERE id = $book_id");
            $book = $bookRes->fetch_assoc();
            $title = $conn->real_escape_string($book['title']);
            $author = $conn->real_escape_string($book['author']);
            $category = $conn->real_escape_string($book['category']);

            // Find matching wishlist items
            $matchesRes = $conn->query("
                SELECT DISTINCT user_id 
                FROM wishlist 
                WHERE (title LIKE '%$title%' OR '$title' LIKE CONCAT('%', title, '%'))
                OR (category = '$category' AND title = '')
            ");

            while ($match = $matchesRes->fetch_assoc()) {
                $target_user_id = $match['user_id'];
                $notifTitle = "Book Available!";
                $msgStr = "The book '" . $book['title'] . "' matching your wishlist is now available in Browse Books.";
                
                $conn->query("INSERT INTO notifications (user_id, type, title, message, related_id, related_type) 
                            VALUES ($target_user_id, 'wishlist_match', '$notifTitle', '$msgStr', $book_id, 'book')");
            }
        }

        header("Location: ../../admin/dashboard.php?msg=" . urlencode("Book $status successfully."));
    } else {
        header("Location: ../../admin/dashboard.php?error=" . urlencode("Error updating book status."));
    }
} else {
    header("Location: ../../admin/dashboard.php");
}
exit();
?>
