<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "boichokro";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// --- TABLE CREATION (Matches legacy/schema.sql) ---

// 1. Users Table
$usersSql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    area VARCHAR(50),
    role ENUM('user', 'library', 'admin') DEFAULT 'user',
    email_verified BOOLEAN DEFAULT FALSE,
    otp_code VARCHAR(6),
    otp_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_area (area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($usersSql) === FALSE) { die("Error creating users table: " . $conn->error); }

// Schema Migration for Users Table (Fix for existing table from previous steps)
$cols = $conn->query("SHOW COLUMNS FROM users");
$existingCols = [];
while($c = $cols->fetch_assoc()) { $existingCols[] = $c['Field']; }

if (!in_array('password_hash', $existingCols)) {
    // Determine if we need to drop old 'password' column or rename it, or just add new one.
    // If 'password' exists, we might want to drop it or ignore it. Let's just add the new columns.
    $conn->query("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER email");
    $conn->query("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NOT NULL AFTER password_hash");
    $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER full_name");
    $conn->query("ALTER TABLE users ADD COLUMN address TEXT AFTER phone");
    $conn->query("ALTER TABLE users ADD COLUMN city VARCHAR(50) AFTER address");
    $conn->query("ALTER TABLE users ADD COLUMN area VARCHAR(50) AFTER city");
    $conn->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'library', 'admin') DEFAULT 'user' AFTER area");
    $conn->query("ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER role");
    $conn->query("ALTER TABLE users ADD COLUMN otp_code VARCHAR(6) AFTER email_verified");
    $conn->query("ALTER TABLE users ADD COLUMN otp_expires DATETIME AFTER otp_code");
    $conn->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'suspended', 'deleted') DEFAULT 'active' AFTER updated_at");
}

// Add profile_image column if missing (older installs)
if (!in_array('profile_image', $existingCols)) {
    $conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) AFTER full_name");
}


// 2. Libraries Table
$libsSql = "CREATE TABLE IF NOT EXISTS libraries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    library_name VARCHAR(100) NOT NULL,
    library_type ENUM('public', 'university') NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    area VARCHAR(50) NOT NULL,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    description TEXT,
    image_url VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_area (area),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($libsSql) === FALSE) { die("Error creating libraries table: " . $conn->error); }

// Schema Migration for Libraries Table (add image_url if missing)
$libColsRes = $conn->query("SHOW COLUMNS FROM libraries");
$libCols = [];
while ($libColsRes && ($c = $libColsRes->fetch_assoc())) { $libCols[] = $c['Field']; }
if (!in_array('image_url', $libCols)) {
    $conn->query("ALTER TABLE libraries ADD COLUMN image_url VARCHAR(255) AFTER description");
}

// 3. Books Table
$booksSql = "CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20),
    category VARCHAR(50),
    description TEXT,
    `condition` ENUM('new', 'like_new', 'good', 'fair', 'poor') DEFAULT 'good',
    listing_type ENUM('sale', 'swap', 'donation') NOT NULL,
    price DECIMAL(10, 2) DEFAULT 0.00,
    swap_interest VARCHAR(255),
    location VARCHAR(100),
    area VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'sold', 'swapped', 'donated', 'available') DEFAULT 'pending',
    images TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at DATETIME,
    approved_by INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_listing_type (listing_type),
    INDEX idx_area (area),
    INDEX idx_category (category),
    INDEX idx_user_id (user_id),
    FULLTEXT idx_search (title, author, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($booksSql) === FALSE) { die("Error creating books table: " . $conn->error); }

// Schema Migration for Books Table (add swap_interest if missing)
$bookColsRes = $conn->query("SHOW COLUMNS FROM books");
$bookCols = [];
while ($bookColsRes && ($c = $bookColsRes->fetch_assoc())) { $bookCols[] = $c['Field']; }
if (!in_array('swap_interest', $bookCols)) {
    $conn->query("ALTER TABLE books ADD COLUMN swap_interest VARCHAR(255) AFTER price");
}

// --- 4. Library Books Table ---
$libBooksSql = "CREATE TABLE IF NOT EXISTS library_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    library_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20),
    category VARCHAR(50),
    description TEXT,
    total_copies INT DEFAULT 1 CHECK (total_copies >= 0),
    available_copies INT DEFAULT 1 CHECK (available_copies >= 0),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (library_id) REFERENCES libraries(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_library_id (library_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($libBooksSql) === FALSE) { die("Error creating library_books table: " . $conn->error); }

// --- 5. Wishlist Table ---
$wishlistSql = "CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200),
    author VARCHAR(100),
    category VARCHAR(50),
    area VARCHAR(50),
    notified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_notified (notified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($wishlistSql) === FALSE) { die("Error creating wishlist table: " . $conn->error); }

// --- 6. Transactions Table ---
$transSql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    book_id INT NOT NULL,
    transaction_type ENUM('sale', 'swap', 'donation') NOT NULL,
    amount DECIMAL(10, 2) DEFAULT 0.00 CHECK (amount >= 0),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_seller_id (seller_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_book_id (book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($transSql) === FALSE) { die("Error creating transactions table: " . $conn->error); }

// --- 7. Library Bookings Table ---
$bookingsSql = "CREATE TABLE IF NOT EXISTS library_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    library_id INT NOT NULL,
    library_book_id INT NOT NULL,
    request_date DATE NOT NULL,
    return_date DATE,
    status ENUM('pending', 'approved', 'rejected', 'issued', 'returned', 'overdue') DEFAULT 'pending',
    issued_at DATETIME,
    returned_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (library_id) REFERENCES libraries(id) ON DELETE CASCADE,
    FOREIGN KEY (library_book_id) REFERENCES library_books(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_library_id (library_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($bookingsSql) === FALSE) { die("Error creating library_bookings table: " . $conn->error); }

// --- 8. Community Posts Table ---
$postsSql = "CREATE TABLE IF NOT EXISTS community_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_type ENUM('review', 'recommendation', 'request') NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    book_title VARCHAR(200),
    book_author VARCHAR(100),
    priority_tag VARCHAR(50),
    likes_count INT DEFAULT 0 CHECK (likes_count >= 0),
    comments_count INT DEFAULT 0 CHECK (comments_count >= 0),
    status ENUM('active', 'reported', 'hidden', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_type (post_type),
    INDEX idx_status (status),
    INDEX idx_priority_tag (priority_tag),
    INDEX idx_user_id (user_id),
    FULLTEXT idx_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($postsSql) === FALSE) { die("Error creating community_posts table: " . $conn->error); }

// --- 9. Post Comments Table ---
$commentsSql = "CREATE TABLE IF NOT EXISTS post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($commentsSql) === FALSE) { die("Error creating post_comments table: " . $conn->error); }

// --- 10. Post Likes Table ---
$likesSql = "CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($likesSql) === FALSE) { die("Error creating post_likes table: " . $conn->error); }

// --- 11. Reports Table ---
$reportsSql = "CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_type ENUM('post', 'comment', 'user', 'book') NOT NULL,
    reported_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by INT,
    reviewed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_reporter_id (reporter_id),
    INDEX idx_reported_type_id (reported_type, reported_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($reportsSql) === FALSE) { die("Error creating reports table: " . $conn->error); }

// --- 12. Chat Messages Table ---
$chatSql = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender_receiver (sender_id, receiver_id),
    INDEX idx_read_status (read_status),
    INDEX idx_receiver_id (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($chatSql) === FALSE) { die("Error creating chat_messages table: " . $conn->error); }

// --- 13. Notifications Table ---
$notifSql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    related_id INT,
    related_type VARCHAR(50),
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_read_status (read_status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($notifSql) === FALSE) { die("Error creating notifications table: " . $conn->error); }

// --- 14. Environmental Impact Table ---
$impactSql = "CREATE TABLE IF NOT EXISTS environmental_impact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    transaction_id INT,
    impact_type ENUM('reused', 'donated') NOT NULL,
    paper_saved_kg DECIMAL(10, 2) DEFAULT 0.00 CHECK (paper_saved_kg >= 0),
    trees_saved DECIMAL(10, 2) DEFAULT 0.00 CHECK (trees_saved >= 0),
    co2_saved_kg DECIMAL(10, 2) DEFAULT 0.00 CHECK (co2_saved_kg >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    INDEX idx_impact_type (impact_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($impactSql) === FALSE) { die("Error creating environmental_impact table: " . $conn->error); }

// --- 15. Admin Logs Table ---
$adminLogSql = "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($adminLogSql) === FALSE) { die("Error creating admin_logs table: " . $conn->error); }

// --- 16. Community Threads Table ---
$communityThreadsSql = "CREATE TABLE IF NOT EXISTS community_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    status ENUM('active','hidden','deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_book_id (book_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($communityThreadsSql) === FALSE) { die("Error creating community_threads table: " . $conn->error); }

// --- 17. Community Comments Table ---
$communityCommentsSql = "CREATE TABLE IF NOT EXISTS community_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES community_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES community_comments(id) ON DELETE CASCADE,
    INDEX idx_thread_id (thread_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($communityCommentsSql) === FALSE) { die("Error creating community_comments table: " . $conn->error); }

// Schema Migration for Community Comments (add image_url if missing)
$ccColsRes = $conn->query("SHOW COLUMNS FROM community_comments");
$ccCols = [];
while ($ccColsRes && ($c = $ccColsRes->fetch_assoc())) { $ccCols[] = $c['Field']; }
if (!in_array('image_url', $ccCols)) {
    $conn->query("ALTER TABLE community_comments ADD COLUMN image_url VARCHAR(255) AFTER content");
}

// --- 18. Community Appreciations Table ---
$communityAppreciationsSql = "CREATE TABLE IF NOT EXISTS community_appreciations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    item_type ENUM('thread','comment') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_appreciation (user_id, item_id, item_type),
    INDEX idx_item (item_id, item_type),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($communityAppreciationsSql) === FALSE) { die("Error creating community_appreciations table: " . $conn->error); }

// --- 19. Swap Requests Table ---
$swapRequestsSql = "CREATE TABLE IF NOT EXISTS swap_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    owner_id INT NOT NULL,
    requested_book_id INT NOT NULL,
    offered_book_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    requester_confirmed BOOLEAN DEFAULT FALSE,
    owner_confirmed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (offered_book_id) REFERENCES books(id) ON DELETE CASCADE,
    INDEX idx_requester (requester_id),
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_swap (requester_id, requested_book_id, offered_book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
if ($conn->query($swapRequestsSql) === FALSE) { die("Error creating swap_requests table: " . $conn->error); }

// Schema Migration for Swap Requests (add confirmation columns if missing)
$srColsRes = $conn->query("SHOW COLUMNS FROM swap_requests");
$srCols = [];
while ($srColsRes && ($c = $srColsRes->fetch_assoc())) { $srCols[] = $c['Field']; }
if (!in_array('requester_confirmed', $srCols)) {
    $conn->query("ALTER TABLE swap_requests ADD COLUMN requester_confirmed BOOLEAN DEFAULT FALSE AFTER status");
}
if (!in_array('owner_confirmed', $srCols)) {
    $conn->query("ALTER TABLE swap_requests ADD COLUMN owner_confirmed BOOLEAN DEFAULT FALSE AFTER requester_confirmed");
}

// --- SEEDING DATA ---

// Seed Admin User
// Select id and password_hash so we can safely verify/update the password
$userCheck = $conn->query("SELECT id, password_hash FROM users WHERE email = 'admin@boichokro.com' LIMIT 1");
if ($userCheck->num_rows == 0) {
    // Create default admin with password: admin123
    $passHash = password_hash('admin123', PASSWORD_DEFAULT);
    $seedUser = "INSERT INTO users (username, email, password_hash, full_name, role, email_verified, status) 
                 VALUES ('admin', 'admin@boichokro.com', '$passHash', 'System Administrator', 'admin', TRUE, 'active')";
    if ($conn->query($seedUser) === TRUE) {
        $adminId = $conn->insert_id;
    } else {
        die("Error seeding admin: " . $conn->error);
    }
} else {
    $adminRow = $userCheck->fetch_assoc();
    $adminId = $adminRow['id'];

    // Ensure admin password matches 'admin123' for existing installs
    if (isset($adminRow['password_hash']) && $adminRow['password_hash'] !== '' && !password_verify('admin123', $adminRow['password_hash'])) {
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password_hash='$newHash' WHERE id=" . (int)$adminId);
    }
}

// Seed Books using legacy schema columns
$bookCheck = $conn->query("SELECT count(*) as count FROM books");
$row = $bookCheck->fetch_assoc();
if ($row['count'] == 0) {
    $stmt = $conn->prepare("INSERT INTO books (user_id, title, author, price, images, listing_type, area, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $type = 'sale';
    $area = 'Downtown';
    $city = 'Dhaka'; // Default city
    
    // Book 1
    $t1 = "The Great Gatsby"; $a1 = "F. Scott Fitzgerald"; $p1 = 12.99; $img1 = "https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=800";
    $stmt->bind_param("issdssss", $adminId, $t1, $a1, $p1, $img1, $type, $area, $city);
    $stmt->execute();
    
    // Book 2
    $t2 = "To Kill a Mockingbird"; $a2 = "Harper Lee"; $p2 = 10.50; $img2 = "https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&q=80&w=800";
    $stmt->bind_param("issdssss", $adminId, $t2, $a2, $p2, $img2, $type, $area, $city);
    $stmt->execute();
    
    $stmt->close();
}

// Seed Libraries
$libCheck = $conn->query("SELECT count(*) as count FROM libraries");
$row = $libCheck->fetch_assoc();
if ($row['count'] == 0) {
    // Needs user_id (using adminId)
    // Fix: We need to match the schema exactly.
    // libraries columns: user_id, library_name, library_type, address, city, area
    $stmt = $conn->prepare("INSERT INTO libraries (user_id, library_name, library_type, address, city, area) VALUES (?, ?, ?, ?, ?, ?)");
    
    $name = "Central City Library"; $type="public"; $addr="123 Main St"; $city="Dhaka"; $area="Motijheel";
    $stmt->bind_param("isssss", $adminId, $name, $type, $addr, $city, $area);
    $stmt->execute();
    
    $name2 = "University Archives"; $type2="university"; $addr2="Campus Rd"; $city2="Dhaka"; $area2="Nilkhet";
    $stmt->bind_param("isssss", $adminId, $name2, $type2, $addr2, $city2, $area2);
    $stmt->execute();
    
    $stmt->close();
}
?>
