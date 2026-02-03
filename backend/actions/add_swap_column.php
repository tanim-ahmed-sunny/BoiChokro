<?php
require_once '../includes/db.php';

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM books LIKE 'swap_interest'");

if ($check->num_rows == 0) {
    echo "Adding swap_interest column...\n";
    $sql = "ALTER TABLE books ADD COLUMN swap_interest VARCHAR(255) DEFAULT NULL AFTER price";
    if ($conn->query($sql) === TRUE) {
        echo "Column swap_interest added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column swap_interest already exists";
}
?>
