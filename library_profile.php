<?php
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php';

$library_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($library_id === 0) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Library not found.</div></div>";
    require_once 'backend/includes/footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT * FROM libraries WHERE id = ?");
$stmt->bind_param("i", $library_id);
$stmt->execute();
$lib = $stmt->get_result()->fetch_assoc();

if (!$lib || $lib['status'] !== 'approved') {
    echo "<div class='container py-5'><div class='alert alert-danger'>Library not found or access has been revoked.</div></div>";
    require_once 'backend/includes/footer.php';
    exit;
}

$books_count = $conn->query("SELECT COUNT(*) as c FROM library_books WHERE library_id = $library_id")->fetch_assoc()['c'];
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : 'all';

$query = "SELECT * FROM library_books WHERE library_id = $library_id AND available_copies > 0";
if ($category !== 'all') {
    $query .= " AND category = '$category'";
}
$query .= " ORDER BY created_at DESC";
$books = $conn->query($query);
$categoriesRes = $conn->query("SELECT DISTINCT category FROM library_books WHERE library_id = $library_id AND category IS NOT NULL AND category != '' ORDER BY category ASC");

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
$is_owner = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$lib['user_id']);
?>

<style>
.lib-image-container { position: relative; display: inline-block; }
.lib-image-overlay { position: absolute; bottom: 10px; right: 10px; background: rgba(var(--bs-primary-rgb), 0.9); color: white; padding: 8px 15px; border-radius: 50px; cursor: pointer; font-size: 0.85rem; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
.lib-image-overlay:hover { background: var(--bs-primary); transform: translateY(-2px); }
</style>

<div class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-3">
                <div class="lib-image-container">
                    <img src="<?php echo htmlspecialchars($lib['image_url'] ?: 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&q=80&w=800'); ?>" class="img-fluid rounded-4 shadow-sm" alt="Library Image" id="libImagePreview">
                    <?php if ($is_owner): ?>
                        <div class="lib-image-overlay" onclick="document.getElementById('libImageInput').click();">
                            <i class="bi bi-camera me-1"></i> Change Photo
                        </div>
                        <form id="libImageForm" action="backend/actions/update_library_image.php" method="POST" enctype="multipart/form-data" class="d-none">
                            <input type="hidden" name="library_id" value="<?php echo $library_id; ?>">
                            <input type="file" id="libImageInput" name="library_image" accept="image/*" onchange="document.getElementById('libImageForm').submit();">
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-9">
                <h1 class="fw-bold display-5 mb-2"><?php echo htmlspecialchars($lib['library_name']); ?></h1>
                <p class="lead text-muted mb-3"><?php echo htmlspecialchars($lib['description'] ?: 'A local community library.'); ?></p>
                <div class="d-flex flex-wrap gap-3 text-secondary">
                    <div><i class="bi bi-geo-alt-fill me-1 text-primary"></i> <?php echo htmlspecialchars($lib['address'] . ', ' . $lib['area'] . ', ' . $lib['city']); ?></div>
                    <div class="vr"></div>
                    <div><i class="bi bi-book-fill me-1 text-primary"></i> <?php echo (int)$books_count; ?> Books Collection</div>
                    <div class="vr"></div>
                    <div><i class="bi bi-layers-fill me-1 text-primary"></i> <?php echo ucfirst($lib['library_type']); ?> Library</div>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Available Collection</h3>
            <div style="min-width: 200px;">
                <form action="library_profile.php" method="GET" id="categoryForm">
                    <input type="hidden" name="id" value="<?php echo $library_id; ?>">
                    <select name="category" class="form-select border-0 shadow-sm rounded-pill px-3" onchange="document.getElementById('categoryForm').submit()">
                        <option value="all" <?php echo $category == 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <?php while ($cat = $categoriesRes->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['category']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
        </div>
        <div class="row g-4">
            <?php if ($books && $books->num_rows > 0): ?>
                <?php while ($book = $books->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 hover-scale transition-all">
                            <div class="card-body p-4">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mb-2"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h4 class="fw-bold mb-1 text-truncate" title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h4>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($book['author']); ?></p>
                                <p class="card-text text-secondary small mb-4 line-clamp-3"><?php echo htmlspecialchars($book['description'] ?: 'No description available.'); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div class="small">
                                        <div class="text-muted">Available</div>
                                        <div class="fw-bold text-success"><?php echo (int)$book['available_copies']; ?> copies</div>
                                    </div>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form action="backend/actions/borrow_request.php" method="POST">
                                            <input type="hidden" name="library_id" value="<?php echo $library_id; ?>">
                                            <input type="hidden" name="book_id" value="<?php echo (int)$book['id']; ?>">
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Request</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-outline-primary rounded-pill px-4">Login to Borrow</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 py-5 text-center">
                    <i class="bi bi-journal-x display-1 text-muted opacity-25"></i>
                    <p class="text-muted mt-3">No books currently available for borrowing.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'backend/includes/footer.php'; ?>
