<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'start_thread') {
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);
        $image_url = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_url = handleImageUpload($_FILES['image'], '../uploads/community/');
        }

        $sql = "INSERT INTO community_threads (user_id, title, content, image_url) 
                VALUES ($user_id, '$title', '$content', '$image_url')";
        
        if ($conn->query($sql)) {
            header("Location: ../../community.php?msg=Discussion started!");
        } else {
            header("Location: ../../community.php?error=Failed to start discussion");
        }
    } 
    
    elseif ($action === 'post_comment') {
        $thread_id = (int)$_POST['thread_id'];
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
        $content = $conn->real_escape_string($_POST['content']);
        $image_url = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_url = handleImageUpload($_FILES['image'], '../uploads/community/');
        }

        $sql = "INSERT INTO community_comments (thread_id, user_id, parent_id, content, image_url) 
                VALUES ($thread_id, $user_id, $parent_id, '$content', '$image_url')";
        
        if ($conn->query($sql)) {
            $redirect = !empty($_POST['redirect_to']) ? '../../' . $_POST['redirect_to'] : "../../thread_view.php?id=$thread_id&msg=Comment posted";
            header("Location: $redirect");
        } else {
            $redirect = !empty($_POST['redirect_to']) ? '../../' . $_POST['redirect_to'] : "../../thread_view.php?id=$thread_id&error=Failed to post comment";
            header("Location: $redirect");
        }
    }

    elseif ($action === 'toggle_appreciate') {
        $item_id = (int)$_POST['item_id'];
        $item_type = $conn->real_escape_string($_POST['item_type']);

        // Toggle logic
        $check = $conn->query("SELECT id FROM community_appreciations WHERE user_id = $user_id AND item_id = $item_id AND item_type = '$item_type'");
        
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM community_appreciations WHERE user_id = $user_id AND item_id = $item_id AND item_type = '$item_type'");
            $status = 'removed';
        } else {
            $conn->query("INSERT INTO community_appreciations (user_id, item_id, item_type) VALUES ($user_id, $item_id, '$item_type')");
            $status = 'added';
        }

        header("Content-Type: application/json");
        echo json_encode(['success' => true, 'status' => $status]);
        exit();
    }
}

/**
 * Handle image upload with validation and processing
 */
function handleImageUpload($file, $upload_dir) {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($file_ext, $allowed)) return '';
    if ($file['size'] > 3145728) return ''; // 3MB limit

    $new_name = uniqid() . '.' . $file_ext;
    $target_path = $upload_dir . $new_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Return relative path for DB, prepending backend/ for root access
        return 'backend/' . str_replace('../', '', $target_path);
    }
    return '';
}
?>
