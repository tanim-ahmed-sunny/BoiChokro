<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donor_name = $conn->real_escape_string($_POST['donor_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $book_quantity = (int)$_POST['book_quantity'];
    $book_types = $conn->real_escape_string($_POST['book_types']);
    $reason = $conn->real_escape_string($_POST['reason']);

    if (empty($donor_name) || empty($email) || empty($phone) || empty($reason)) {
        header("Location: ../../donate.php?error=" . urlencode("Please fill in all required fields."));
        exit();
    }

    $sql = "INSERT INTO donation_requests (donor_name, email, phone, book_quantity, book_types, reason, status) 
            VALUES ('$donor_name', '$email', '$phone', $book_quantity, '$book_types', '$reason', 'pending')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../../donate.php?msg=" . urlencode("Thank you! Your donation request has been submitted. Our team will contact you soon."));
    } else {
        header("Location: ../../donate.php?error=" . urlencode("Error: " . $conn->error));
    }
} else {
    header("Location: ../../donate.php");
}
exit();
?>
