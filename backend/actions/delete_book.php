<?php
require_once '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../../profile.php");
    exit();
}

$book_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// First, verify ownership (unless admin)
if ($user_role !== 'admin') {
    $check = $conn->prepare("SELECT id FROM books WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $book_id, $user_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        $check->close();
        header("Location: ../../profile.php?error=You+don't+have+permission+to+delete+this+book");
        exit();
    }
    $check->close();
}

// Delete all related swap requests first to avoid foreign key constraint
$deleteSwaps = $conn->prepare("DELETE FROM swap_requests WHERE requested_book_id = ? OR offered_book_id = ?");
$deleteSwaps->bind_param("ii", $book_id, $book_id);
$deleteSwaps->execute();
$deleteSwaps->close();

// Now delete the book
$deleteBook = $conn->prepare("DELETE FROM books WHERE id = ?" . ($user_role !== 'admin' ? " AND user_id = ?" : ""));
if ($user_role !== 'admin') {
    $deleteBook->bind_param("ii", $book_id, $user_id);
} else {
    $deleteBook->bind_param("i", $book_id);
}

if ($deleteBook->execute() && $deleteBook->affected_rows > 0) {
    $deleteBook->close();
    $conn->close();
    header("Location: ../../profile.php?msg=Book+deleted+successfully");
} else {
    $deleteBook->close();
    $conn->close();
    header("Location: ../../profile.php?error=Failed+to+delete+book");
}
exit();
?>
