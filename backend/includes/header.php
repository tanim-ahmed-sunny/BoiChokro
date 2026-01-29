<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

// Role-based Access Control Redirects
$currentPage = basename($_SERVER['PHP_SELF']);
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin' && $currentPage !== 'logout.php') {
        header("Location: admin/dashboard.php");
        exit();
    }
    if ($_SESSION['role'] === 'library' && $currentPage !== 'library_dashboard.php' && $currentPage !== 'logout.php') {
        header("Location: library_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boichokro - Connect, Share, Read</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
        <i class="bi bi-book-half text-primary fs-3"></i>
        <span>Boichokro</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center gap-3">
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        
        <?php if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'library')): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $currentPage == 'books.php' ? 'active' : ''; ?>" href="books.php">Browse Books</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo $currentPage == 'libraries.php' ? 'active' : ''; ?>" href="libraries.php">Libraries</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo ($currentPage == 'community.php' || $currentPage == 'thread_view.php') ? 'active' : ''; ?>" href="community.php">Community</a>
            </li>
        <?php endif; ?>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] !== 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-primary fw-medium" href="add_book.php"><i class="bi bi-plus-lg me-1"></i>Post a Book</a>
                </li>
            <?php endif; ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 32px; height: 32px; background: #f0fdf4; border: 1px solid #dcfce7;">
                        <?php if (isset($_SESSION['profile_image']) && $_SESSION['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" class="w-100 h-100 object-fit-cover">
                        <?php else: ?>
                            <i class="bi bi-person-fill text-primary"></i>
                        <?php endif; ?>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                    <li><h6 class="dropdown-header">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></h6></li>
                    
                    <?php if($_SESSION['role'] !== 'admin'): ?>
                        <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                        <li><a class="dropdown-item" href="profile.php">My Books</a></li>
                    <?php endif; ?>

                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-bold text-primary" href="admin/dashboard.php">Admin Dashboard</a></li>
                    <?php endif; ?>

                    <?php if($_SESSION['role'] === 'library'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-bold text-primary" href="library_dashboard.php">Library Dashboard</a></li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item ms-lg-2">
                <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-primary rounded-pill px-4" href="register.php">Get Started</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="flex-grow-1">
