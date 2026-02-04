<?php
require_once '../includes/db.php';

echo "Initializing community tables...\n";

// 1. Community Threads
$sql = "CREATE TABLE IF NOT EXISTS community_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'archived', 'reported') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'community_threads' created successfully\n";
} else {
    echo "Error creating table community_threads: " . $conn->error . "\n";
}

// 2. Community Comments
$sql = "CREATE TABLE IF NOT EXISTS community_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES community_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES community_comments(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'community_comments' created successfully\n";
} else {
    echo "Error creating table community_comments: " . $conn->error . "\n";
}

// 3. Community Appreciations (Likes)
$sql = "CREATE TABLE IF NOT EXISTS community_appreciations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    item_type ENUM('thread', 'comment') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, item_id, item_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'community_appreciations' created successfully\n";
} else {
    echo "Error creating table community_appreciations: " . $conn->error . "\n";
}

echo "Initialization complete.";
?>
