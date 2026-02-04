<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Create swap request
if ($action === 'create_swap_request') {
    $requested_book_id = (int)($_POST['requested_book_id'] ?? 0);
    $offered_book_id = (int)($_POST['offered_book_id'] ?? 0);
    
    // Validate books exist
    $requested_book = $conn->query("SELECT user_id, listing_type FROM books WHERE id = $requested_book_id")->fetch_assoc();
    $offered_book = $conn->query("SELECT user_id, listing_type FROM books WHERE id = $offered_book_id AND user_id = $user_id")->fetch_assoc();
    
    if (!$requested_book || !$offered_book) {
        echo json_encode(['success' => false, 'error' => 'Invalid books']);
        exit;
    }
    
    if ($requested_book['listing_type'] !== 'swap' || $offered_book['listing_type'] !== 'swap') {
        echo json_encode(['success' => false, 'error' => 'Both books must be for swap']);
        exit;
    }
    
    $owner_id = $requested_book['user_id'];
    
    if ($owner_id == $user_id) {
        echo json_encode(['success' => false, 'error' => 'Cannot swap with yourself']);
        exit;
    }
    
    // Check for existing pending request
    $existing = $conn->query("SELECT id FROM swap_requests WHERE requester_id = $user_id AND requested_book_id = $requested_book_id AND status = 'pending'")->num_rows;
    
    if ($existing > 0) {
        echo json_encode(['success' => false, 'error' => 'You already have a pending request for this book']);
        exit;
    }
    
    // Create request
    $stmt = $conn->prepare("INSERT INTO swap_requests (requester_id, owner_id, requested_book_id, offered_book_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $user_id, $owner_id, $requested_book_id, $offered_book_id);
    
    if ($stmt->execute()) {
        $request_id = $stmt->insert_id;

        // Create notification for owner
        $reqUser = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
        $b1 = $conn->query("SELECT title FROM books WHERE id = $requested_book_id")->fetch_assoc();
        $b2 = $conn->query("SELECT title FROM books WHERE id = $offered_book_id")->fetch_assoc();

        $requesterName = $reqUser['username'] ?? 'Someone';
        $requestedTitle = $b1['title'] ?? 'a book';
        $offeredTitle = $b2['title'] ?? 'a book';

        $title = "New swap request";
        $message = "@$requesterName wants to swap \"$offeredTitle\" for \"$requestedTitle\".";
        $nStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, related_id, related_type, read_status) VALUES (?, 'swap_request', ?, ?, ?, 'swap_request', 0)");
        $nStmt->bind_param("issi", $owner_id, $title, $message, $request_id);
        $nStmt->execute();

        echo json_encode(['success' => true, 'message' => 'Swap request sent!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create request: ' . $conn->error]);
    }
    exit;
}

// Accept swap request
if ($action === 'accept_swap') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $notification_id = (int)($_POST['notification_id'] ?? 0);
    
    $request = $conn->query("SELECT * FROM swap_requests WHERE id = $request_id AND owner_id = $user_id AND status = 'pending'")->fetch_assoc();
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    // Update status
    if ($conn->query("UPDATE swap_requests SET status = 'accepted' WHERE id = $request_id")) {
        if ($notification_id > 0) {
            $conn->query("UPDATE notifications SET read_status = 1 WHERE id = $notification_id AND user_id = $user_id");
        }

        // Notify requester
        $ownerNameRow = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
        $ownerName = $ownerNameRow['username'] ?? 'The owner';
        $title = "Swap request accepted";
        $message = "@$ownerName accepted your swap request. Confirm exchange after you meet.";
        $nStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, related_id, related_type, read_status) VALUES (?, 'swap_status', ?, ?, ?, 'swap_request', 0)");
        $nStmt->bind_param("issi", $request['requester_id'], $title, $message, $request_id);
        $nStmt->execute();

        // Get both users' phone numbers
        $requester = $conn->query("SELECT phone FROM users WHERE id = {$request['requester_id']}")->fetch_assoc();
        $owner = $conn->query("SELECT phone FROM users WHERE id = $user_id")->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Swap accepted!',
            'requester_phone' => $requester['phone'] ?? 'Not available',
            'owner_phone' => $owner['phone'] ?? 'Not available'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }
    exit;
}

// Reject swap request
if ($action === 'reject_swap') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $notification_id = (int)($_POST['notification_id'] ?? 0);
    
    $request = $conn->query("SELECT * FROM swap_requests WHERE id = $request_id AND owner_id = $user_id AND status = 'pending'")->fetch_assoc();
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    if ($conn->query("UPDATE swap_requests SET status = 'rejected' WHERE id = $request_id")) {
        if ($notification_id > 0) {
            $conn->query("UPDATE notifications SET read_status = 1 WHERE id = $notification_id AND user_id = $user_id");
        }

        // Notify requester
        $ownerNameRow = $conn->query("SELECT username FROM users WHERE id = $user_id")->fetch_assoc();
        $ownerName = $ownerNameRow['username'] ?? 'The owner';
        $title = "Swap request rejected";
        $message = "@$ownerName rejected your swap request.";
        $nStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, related_id, related_type, read_status) VALUES (?, 'swap_status', ?, ?, ?, 'swap_request', 0)");
        $nStmt->bind_param("issi", $request['requester_id'], $title, $message, $request_id);
        $nStmt->execute();

        echo json_encode(['success' => true, 'message' => 'Request rejected']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }
    exit;
}

// Get user's swap books
if ($action === 'get_user_swap_books') {
    // Broaden the search to any 'available' or just listing_type='swap'
    $books = $conn->query("SELECT id, title, author, images as cover_image FROM books WHERE user_id = $user_id AND listing_type = 'swap' ORDER BY created_at DESC");
    
    $result = [];
    while ($book = $books->fetch_assoc()) {
        $result[] = $book;
    }
    
    echo json_encode(['success' => true, 'books' => $result]);
    exit;
}

// Get swap requests (for notifications)
if ($action === 'get_swap_requests') {
    $requests = $conn->query("
        SELECT sr.*, 
               u.username as requester_name,
               b1.title as requested_book_title,
               b2.title as offered_book_title,
               b2.images as offered_book_image
        FROM swap_requests sr
        JOIN users u ON sr.requester_id = u.id
        JOIN books b1 ON sr.requested_book_id = b1.id
        JOIN books b2 ON sr.offered_book_id = b2.id
        WHERE sr.owner_id = $user_id AND sr.status = 'pending'
        ORDER BY sr.created_at DESC
    ");
    
    $result = [];
    while ($req = $requests->fetch_assoc()) {
        $result[] = $req;
    }
    
    echo json_encode(['success' => true, 'requests' => $result]);
    exit;
}

// Confirm exchange
if ($action === 'confirm_exchange') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    
    // Find the request
    $request = $conn->query("SELECT * FROM swap_requests WHERE id = $request_id AND status = 'accepted'")->fetch_assoc();
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Invalid or unaccepted request']);
        exit;
    }
    
    if ($request['requester_id'] == $user_id) {
        $conn->query("UPDATE swap_requests SET requester_confirmed = 1 WHERE id = $request_id");
    } elseif ($request['owner_id'] == $user_id) {
        $conn->query("UPDATE swap_requests SET owner_confirmed = 1 WHERE id = $request_id");
    } else {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    // Check if both confirmed
    $updated = $conn->query("SELECT * FROM swap_requests WHERE id = $request_id")->fetch_assoc();
    if ($updated['requester_confirmed'] && $updated['owner_confirmed']) {
        // Remove both books from system after both confirmed
        $requested_book_id = $updated['requested_book_id'];
        $offered_book_id = $updated['offered_book_id'];
        
        $conn->query("DELETE FROM books WHERE id IN ($requested_book_id, $offered_book_id)");

        // Notify both users
        $title = "Swap completed";
        $message = "Swap completed and both books were removed from the system.";
        $n1 = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, related_id, related_type, read_status) VALUES (?, 'swap_completed', ?, ?, ?, 'swap_request', 0)");
        $n1->bind_param("issi", $updated['requester_id'], $title, $message, $request_id);
        $n1->execute();
        $n2 = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, related_id, related_type, read_status) VALUES (?, 'swap_completed', ?, ?, ?, 'swap_request', 0)");
        $n2->bind_param("issi", $updated['owner_id'], $title, $message, $request_id);
        $n2->execute();
        
        echo json_encode(['success' => true, 'message' => 'Exchange confirmed! Both books have been removed from listings.', 'completed' => true]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Your confirmation has been recorded. Waiting for the other party.', 'completed' => false]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>
