<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $library_id = (int)$_POST['library_id'];
    $book_id = (int)$_POST['book_id'];

    // Basic Validation
    if ($library_id <= 0 || $book_id <= 0) {
        header("Location: ../../libraries.php?error=Invalid request");
        exit;
    }

    // Check if user already has a PENDING request for this book?
    // Or issued? To prevent spamming.
    $check = $conn->prepare("SELECT id FROM library_bookings WHERE user_id = ? AND library_book_id = ? AND status IN ('pending', 'issued')");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        header("Location: ../../library_profile.php?id=$library_id&error=You already have an active request or loan for this book.");
        exit;
    }

    // Insert Request
    // Default return date not set yet? Or set a desired one? 
    // Schema says return_date is DATE (nullable?). Step 12: `return_date DATE`. 
    // Let's set request_date to NOW. Return date can be set by library upon approval or defaulted now.
    // Let's default return date to NULL or +14 days from NOW as a "desired" date?
    // Better: Leave return_date NULL until issued, or just set a default +14 days request.
    $request_date = date('Y-m-d');
    $status = 'pending';
    
    $stmt = $conn->prepare("INSERT INTO library_bookings (user_id, library_id, library_book_id, request_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $user_id, $library_id, $book_id, $request_date, $status);

    if ($stmt->execute()) {
        header("Location: ../../library_profile.php?id=$library_id&success=Request sent successfully! Awaiting library approval.");
    } else {
        header("Location: ../../library_profile.php?id=$library_id&error=Failed to send request.");
    }

} else {
    header("Location: ../../index.php");
}
?>
