<?php
require_once '../includes/db.php';
session_start();

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $post_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if already liked check could be here, but using INSERT IGNORE handles it roughly.
    // Ideally we check.
    $check = $conn->query("SELECT id FROM post_likes WHERE post_id=$post_id AND user_id=$user_id");
    if($check->num_rows == 0) {
        $conn->query("INSERT INTO post_likes (post_id, user_id) VALUES ($post_id, $user_id)");
        $conn->query("UPDATE community_posts SET likes_count = likes_count + 1 WHERE id=$post_id");
    }
    
    header("Location: ../../community.php");
} else {
    header("Location: ../../community.php");
}
exit();
?>
