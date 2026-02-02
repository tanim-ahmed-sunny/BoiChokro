<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

// Check login and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'library' || $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit();
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                    <h3 class="fw-bold text-primary">Post a Book</h3>
                    <p class="text-muted">Share your knowledge with others</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <?php if($msg): ?>
                        <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($msg); ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-circle-fill me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="backend/actions/add_book.php" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Book Title</label>
                                <input type="text" name="title" class="form-control bg-light border-0" required placeholder="e.g. The Alchemist">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Author</label>
                                <input type="text" name="author" class="form-control bg-light border-0" required placeholder="e.g. Paulo Coelho">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">ISBN (Optional)</label>
                                <input type="text" name="isbn" class="form-control bg-light border-0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Category</label>
                                <select name="category" class="form-select bg-light border-0">
                                    <option value="Fiction">Fiction</option>
                                    <option value="Non-Fiction">Non-Fiction</option>
                                    <option value="Educational">Educational</option>
                                    <option value="Comics">Comics</option>
                                    <option value="History">History</option>
                                    <option value="Sci-Fi">Sci-Fi</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Condition</label>
                                <select name="condition" class="form-select bg-light border-0">
                                    <option value="new">New</option>
                                    <option value="like_new">Like New</option>
                                    <option value="good" selected>Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="poor">Poor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Listing Type</label>
                                <select name="listing_type" class="form-select bg-light border-0" id="listingType">
                                    <option value="sale">For Sale</option>
                                    <option value="swap">Swap / Exchange</option>
                                </select>
                            </div>

                            <div class="col-md-12" id="priceDiv">
                                <label class="form-label fw-bold small">Price (Tk)</label>
                                <input type="number" step="0.01" name="price" class="form-control bg-light border-0" value="0.00">
                            </div>

                            <div class="col-md-12" id="swapDiv" style="display: none;">
                                <label class="form-label fw-bold small">What do you want in exchange?</label>
                                <input type="text" name="swap_interest" class="form-control bg-light border-0" placeholder="e.g. Any Fiction book, or specific title...">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Cover Image</label>
                                <input type="file" name="image" class="form-control bg-light border-0" accept="image/*">
                                <div class="form-text text-muted">Upload a clear photo of the book (Max 5MB).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">City</label>
                                <input type="text" name="city" class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Area</label>
                                <input type="text" name="area" class="form-control bg-light border-0" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Description</label>
                                <textarea name="description" class="form-control bg-light border-0" rows="4" placeholder="Describe the book's contents, specific condition notes, etc."></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Post Listing</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('listingType').addEventListener('change', function() {
    const priceDiv = document.getElementById('priceDiv');
    const swapDiv = document.getElementById('swapDiv');
    
    priceDiv.style.display = 'none';
    swapDiv.style.display = 'none';
    
    if (this.value === 'sale') {
        priceDiv.style.display = 'block';
    } else if (this.value === 'swap') {
        swapDiv.style.display = 'block';
        priceDiv.querySelector('input').value = 0;
    } else if (this.value === 'donation') {
        priceDiv.querySelector('input').value = 0;
    }
});
</script>

<?php require_once 'backend/includes/footer.php'; ?>
