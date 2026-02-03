<?php
require_once '../includes/db.php';
session_start();

// Ensure user is logged in and is a library
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'library') {
    die("Unauthorized access");
}

$library_id = 0; // Need to fetch library_id associated with this user
$stmt = $conn->prepare("SELECT id FROM libraries WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $library_id = $result->fetch_assoc()['id'];
} else {
    // Should handle case where library profile isn't created yet, but for now assuming it exists
    // Or redirect to create library page? For now, we'll error out.
    die("Library profile not found for this user.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_book') {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isbn = $_POST['isbn'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $total_copies = (int)$_POST['quantity'];
        
        $stmt = $conn->prepare("INSERT INTO library_books (library_id, title, author, isbn, category, description, total_copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssii", $library_id, $title, $author, $isbn, $category, $description, $total_copies, $total_copies);
        
        if ($stmt->execute()) {
            header("Location: ../../library_dashboard.php?success=Book added successfully");
        } else {
            header("Location: ../../library_dashboard.php?error=Failed to add book");
        }
    } elseif ($action === 'lend_book') {
        $book_id = (int)$_POST['book_id'];
        $user_email = $_POST['user_email'];
        $return_date = $_POST['return_date'];
        
        // Find user by email
        $user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $user_stmt->bind_param("s", $user_email);
        $user_stmt->execute();
        $user_res = $user_stmt->get_result();
        
        if ($user_res->num_rows === 0) {
             header("Location: ../../library_dashboard.php?error=User not found");
             exit;
        }
        $target_user_id = $user_res->fetch_assoc()['id'];

        // Check availability
        $book_check = $conn->prepare("SELECT available_copies FROM library_books WHERE id = ? AND library_id = ?");
        $book_check->bind_param("ii", $book_id, $library_id);
        $book_check->execute();
        $book_res = $book_check->get_result();
        if ($book_res->num_rows === 0) {
             header("Location: ../../library_dashboard.php?error=Book not found");
             exit;
        }
        $book_data = $book_res->fetch_assoc();
        
        if ($book_data['available_copies'] < 1) {
            header("Location: ../../library_dashboard.php?error=No copies available");
            exit;
        }

        // Start Transaction
        $conn->begin_transaction();
        try {
            // Create booking
            $status = 'issued';
            $today = date('Y-m-d');
            $insert = $conn->prepare("INSERT INTO library_bookings (user_id, library_id, library_book_id, request_date, return_date, status, issued_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $insert->bind_param("iiisss", $target_user_id, $library_id, $book_id, $today, $return_date, $status);
            $insert->execute();

            // Decrease copies
            $update = $conn->prepare("UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?");
            $update->bind_param("i", $book_id);
            $update->execute();

            $conn->commit();
            header("Location: ../../library_dashboard.php?success=Book lent successfully");
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: ../../library_dashboard.php?error=Transaction failed");
        }

    } elseif ($action === 'return_book') {
        $booking_id = (int)$_POST['booking_id'];
        
        // Validate booking belongs to this library
        $check = $conn->prepare("SELECT library_book_id FROM library_bookings WHERE id = ? AND library_id = ? AND status = 'issued'");
        $check->bind_param("ii", $booking_id, $library_id);
        $check->execute();
        $res = $check->get_result();
        
        if ($res->num_rows === 0) {
            header("Location: ../../library_dashboard.php?error=Invalid booking");
            exit;
        }
        $booking = $res->fetch_assoc();
        $book_id = $booking['library_book_id'];

        // Start Transaction
        $conn->begin_transaction();
        try {
            // Update booking
            $update_booking = $conn->prepare("UPDATE library_bookings SET status = 'returned', returned_at = NOW() WHERE id = ?");
            $update_booking->bind_param("i", $booking_id);
            $update_booking->execute();

            // Increase copies
            $update_book = $conn->prepare("UPDATE library_books SET available_copies = available_copies + 1 WHERE id = ?");
            $update_book->bind_param("i", $book_id);
            $update_book->execute();

            $conn->commit();
            header("Location: ../../library_dashboard.php?success=Book returned successfully");
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: ../../library_dashboard.php?error=Transaction failed");
        }
    } elseif ($action === 'approve_request') {
        $booking_id = (int)$_POST['booking_id'];
        $return_date = $_POST['return_date'];
        
        // Validate booking
        $check = $conn->prepare("SELECT library_book_id FROM library_bookings WHERE id = ? AND library_id = ? AND status = 'pending'");
        $check->bind_param("ii", $booking_id, $library_id);
        $check->execute();
        $res = $check->get_result();
        
        if ($res->num_rows === 0) {
            header("Location: ../../library_dashboard.php?error=Invalid request");
            exit;
        }
        $booking = $res->fetch_assoc();
        $book_id = $booking['library_book_id'];

        // Check stock again
        $book_check = $conn->prepare("SELECT available_copies FROM library_books WHERE id = ?");
        $book_check->bind_param("i", $book_id);
        $book_check->execute();
        $stock = $book_check->get_result()->fetch_assoc();
        
        if ($stock['available_copies'] < 1) {
            header("Location: ../../library_dashboard.php?error=No copies available to approve this request");
            exit;
        }

        $conn->begin_transaction();
        try {
            // Update Booking status
            $update_booking = $conn->prepare("UPDATE library_bookings SET status = 'issued', return_date = ?, issued_at = NOW() WHERE id = ?");
            $update_booking->bind_param("si", $return_date, $booking_id);
            $update_booking->execute();

            // Decrease Stock
            $update_stock = $conn->prepare("UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?");
            $update_stock->bind_param("i", $book_id);
            $update_stock->execute();
            
            $conn->commit();
            header("Location: ../../library_dashboard.php?success=Request approved and book issued");
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: ../../library_dashboard.php?error=Approval failed");
        }

    } elseif ($action === 'reject_request') {
        $booking_id = (int)$_POST['booking_id'];
        
        // Validate
        $check = $conn->prepare("SELECT id FROM library_bookings WHERE id = ? AND library_id = ? AND status = 'pending'");
        $check->bind_param("ii", $booking_id, $library_id);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
             header("Location: ../../library_dashboard.php?error=Invalid request");
             exit;
        }
        
        $update = $conn->prepare("UPDATE library_bookings SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $booking_id);
        if ($update->execute()) {
            header("Location: ../../library_dashboard.php?success=Request rejected");
        } else {
            header("Location: ../../library_dashboard.php?error=Action failed");
        }
    }
}
?>
