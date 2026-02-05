<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = (int)($_POST['book_id'] ?? 0);

if (!$book_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid book ID']);
    exit;
}

// Verify book belongs to user and is for sale
$stmt = $conn->prepare("SELECT id, title, listing_type FROM books WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    echo json_encode(['success' => false, 'error' => 'Book not found or unauthorized']);
    exit;
}

// Start Transaction
$conn->begin_transaction();

try {
    // Update status to 'sold'
    $updateStmt = $conn->prepare("UPDATE books SET status = 'sold' WHERE id = ?");
    $updateStmt->bind_param("i", $book_id);
    $updateStmt->execute();

    // Record Environmental Impact
    // Impact Metrics per Book: 0.05 trees, 0.5kg paper, 1.0kg CO2
    $impactStmt = $conn->prepare("INSERT INTO environmental_impact (book_id, transaction_id, impact_type, paper_saved_kg, trees_saved, co2_saved_kg) VALUES (?, NULL, 'reused', 0.5, 0.05, 1.0)");
    $impactStmt->bind_param("i", $book_id);
    $impactStmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Book marked as sold! You just helped save a tree.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to record sale: ' . $e->getMessage()]);
}
?>
