<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$userQ = $conn->query("SELECT role FROM users WHERE id=$admin_id");
$user = $userQ->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    die("Access Denied.");
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$success = false;
$msg = "";

switch ($action) {
    case 'revoke_user':
        // Set user to suspended
        if ($conn->query("UPDATE users SET status='suspended' WHERE id=$id")) {
            $success = true;
            $msg = "User suspended successfully.";
        }
        break;

    case 'activate_user':
        // Set user to active
        if ($conn->query("UPDATE users SET status='active' WHERE id=$id")) {
            $success = true;
            $msg = "User activated successfully.";
        }
        break;

    case 'revoke_book':
        // Set book to rejected (revoked)
        if ($conn->query("UPDATE books SET status='rejected' WHERE id=$id")) {
            $success = true;
            $msg = "Book revoked successfully.";
        }
        break;

    case 'revoke_library':
        // Set library status to rejected
        if ($conn->query("UPDATE libraries SET status='rejected' WHERE id=$id")) {
            $success = true;
            $msg = "Library access revoked.";
        }
        break;

    case 'manage_report':
        $status = $_POST['status'] ?? 'reviewed';
        if ($conn->query("UPDATE reports SET status='$status', reviewed_by=$admin_id, reviewed_at=NOW() WHERE id=$id")) {
            $success = true;
            $msg = "Report marked as $status.";
        }
        break;
    case 'update_donation_status':
        $status = $_POST['status'] ?? 'pending';
        if ($conn->query("UPDATE donation_requests SET status='$status' WHERE id=$id")) {
            $success = true;
            $msg = "Donation request marked as $status.";
        }
        break;
}

$tab = $_POST['tab'] ?? 'dashboard';
if ($success) {
    header("Location: ../../admin/dashboard.php?tab=$tab&msg=" . urlencode($msg));
} else {
    header("Location: ../../admin/dashboard.php?tab=$tab&error=" . urlencode("Operation failed or invalid action."));
}
exit();
?>
