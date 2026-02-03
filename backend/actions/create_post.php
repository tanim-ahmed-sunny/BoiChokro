<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $type = $conn->real_escape_string($_POST['post_type']);
    $tag = 'general'; // Default tag

    $sql = "INSERT INTO community_posts (user_id, post_type, title, content, priority_tag, status) 
            VALUES ($user_id, '$type', '$title', '$content', '$tag', 'active')";
            
    if ($conn->query($sql) === TRUE) {
        header("Location: ../../community.php?msg=" . urlencode("Post created successfully!"));
    } else {
         header("Location: ../../community.php?error=" . urlencode("Error creating post."));
    }
} else {
    header("Location: ../../community.php");
}
exit();
?>
