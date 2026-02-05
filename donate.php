<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<div class="bg-light py-5">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-serif" style="color: var(--text-dark);">Donate Your Books</h1>
                    <p class="lead text-muted">Give your books a second home and help build our community library.</p>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-12 p-4 p-md-5">
                            <?php if($msg): ?>
                                <div class="alert alert-success d-flex align-items-center rounded-3 border-0 shadow-sm mb-4">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div><?php echo htmlspecialchars($msg); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($error): ?>
                                <div class="alert alert-danger d-flex align-items-center rounded-3 border-0 shadow-sm mb-4">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                </div>
                            <?php endif; ?>

                            <form action="backend/actions/submit_donation.php" method="POST">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-muted">Full Name</label>
                                        <input type="text" name="donor_name" class="form-control bg-light border-0 py-3 rounded-3" required placeholder="Enter your full name">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Email Address</label>
                                        <input type="email" name="email" class="form-control bg-light border-0 py-3 rounded-3" required placeholder="you@example.com">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Contact Number</label>
                                        <input type="tel" name="phone" class="form-control bg-light border-0 py-3 rounded-3" required placeholder="e.g. 01XXXXXXXXX">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Approximate Number of Books</label>
                                        <input type="number" name="book_quantity" class="form-control bg-light border-0 py-3 rounded-3" value="1" min="1">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">Types of Books</label>
                                        <input type="text" name="book_types" class="form-control bg-light border-0 py-3 rounded-3" placeholder="e.g. Fiction, Educational, Sci-Fi">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-muted">Why do you want to donate?</label>
                                        <textarea name="reason" rows="4" class="form-control bg-light border-0 py-3 rounded-3" required placeholder="Tell us a little bit about your donation..."></textarea>
                                    </div>

                                    <div class="col-12 pt-2">
                                        <button type="submit" class="btn w-100 py-3 rounded-pill fw-bold shadow-sm" style="background-color: var(--primary-green); color: white; border: none;">
                                            <i class="bi bi-heart-fill me-2"></i>Submit Donation Request
                                        </button>
                                        <p class="text-center text-muted small mt-3 px-4">
                                            Our team will review your request and contact you within 48 hours for the pickup/drop-off process.
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'backend/includes/footer.php'; ?>
