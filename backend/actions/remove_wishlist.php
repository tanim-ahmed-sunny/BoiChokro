<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../../profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$wishlist_id = (int)$_GET['id'];

$sql = "DELETE FROM wishlist WHERE id = $wishlist_id AND user_id = $user_id";

if ($conn->query($sql)) {
    header("Location: ../../profile.php?msg=" . urlencode("Wishlist item removed"));
} else {
    header("Location: ../../profile.php?error=" . urlencode("Error removing item"));
}
exit();
?>
