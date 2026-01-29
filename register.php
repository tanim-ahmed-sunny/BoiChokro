<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="text-center mb-4">
                <i class="bi bi-book-half text-primary display-4"></i>
                <h2 class="fw-bold mt-2">Create an Account</h2>
                <p class="text-muted">Join the Boichokro community today</p>
            </div>

            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="backend/actions/register.php">
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-medium">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 bg-light ps-0" id="full_name" name="full_name" required placeholder="John Doe">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label fw-medium">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 bg-light ps-0" id="username" name="username" required placeholder="johndoe123">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium">Email address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 bg-light ps-0" id="email" name="email" required placeholder="you@example.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 bg-light ps-0" id="password" name="password" required placeholder="••••••••">
                            </div>
                        </div>
                         <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-medium">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 bg-light ps-0" id="confirm_password" name="confirm_password" required placeholder="••••••••">
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 rounded-3">Create Account</button>
                        </div>
                    </form>
                    <div class="text-center mt-4 pt-3 border-top">
                         <p class="text-muted mb-0">Already have an account? <a href="login.php" class="text-primary fw-semibold text-decoration-none">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'backend/includes/footer.php'; ?>
