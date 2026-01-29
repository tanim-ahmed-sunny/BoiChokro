<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

// Fetch some featured books
$featuredBooks = $conn->query("SELECT * FROM books WHERE status = 'approved' ORDER BY created_at DESC LIMIT 4");

// Fetch counts for some stats
$bookCount = $conn->query("SELECT COUNT(*) as count FROM books WHERE status = 'approved'")->fetch_assoc()['count'];
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$libraryCount = $conn->query("SELECT COUNT(*) as count FROM libraries WHERE status = 'approved'")->fetch_assoc()['count'];
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container position-relative z-1">
        <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInDown">Welcome to <span class="text-primary">Boichokro</span></h1>
        <p class="lead mb-5 animate__animated animate__fadeInUp">The ultimate community for book lovers to swap, share, and discover new reads.</p>
        <div class="d-flex justify-content-center gap-3 animate__animated animate__fadeInUp animate__delay-1s">
            <a href="books.php" class="btn btn-primary rounded-pill px-5 py-3 fs-5 shadow-lg">Browse Books</a>
            <a href="community.php" class="btn btn-outline-light rounded-pill px-5 py-3 fs-5">Join Community</a>
        </div>
    </div>
    <div class="prompt-decoration"></div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $bookCount; ?>+</div>
                    <div class="stat-label">Books Available</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card border-start border-end">
                    <div class="stat-number"><?php echo $userCount; ?>+</div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $libraryCount; ?>+</div>
                    <div class="stat-label">Partner Libraries</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Books -->
<section class="py-5 bg-communities">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-1">Featured Books</h2>
                <p class="text-muted">Explore the latest additions to our community library.</p>
            </div>
            <a href="books.php" class="text-primary fw-bold text-decoration-none mb-2">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        
        <div class="row g-4">
            <?php if ($featuredBooks && $featuredBooks->num_rows > 0): ?>
                <?php while($row = $featuredBooks->fetch_assoc()): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm hover-scale overflow-hidden">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($row['images'] ?: 'https://via.placeholder.com/300x400?text=No+Image'); ?>" class="card-img-top object-fit-cover" style="height: 250px;">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-primary text-white shadow-sm rounded-pill px-3 py-2 fw-bold">
                                        <?php if($row['listing_type'] == 'swap'): ?>Swap
                                        <?php elseif($row['listing_type'] == 'donation'): ?>Free
                                        <?php else: ?>৳<?php echo $row['price']; ?><?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-truncate mb-1"><?php echo htmlspecialchars($row['title']); ?></h6>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($row['author']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No books currently featured. Be the first to list one!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How it Works -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">How It Works</h2>
        <div class="row g-5">
            <div class="col-md-4 text-center">
                <div class="feature-icon mx-auto"><i class="bi bi-search"></i></div>
                <h4 class="fw-bold mb-3">Discover</h4>
                <p class="text-muted text-center px-lg-4">Browse through thousands of books listed by our community members or partner libraries.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-icon mx-auto"><i class="bi bi-arrow-left-right"></i></div>
                <h4 class="fw-bold mb-3">Swap or Buy</h4>
                <p class="text-muted text-center px-lg-4">Connect with other book lovers to swap titles you've read for something new, or buy at great prices.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-icon mx-auto"><i class="bi bi-people"></i></div>
                <h4 class="fw-bold mb-3">Join the Club</h4>
                <p class="text-muted text-center px-lg-4">Engage in discussions, write reviews, and share your reading journey in our vibrant community.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'backend/includes/footer.php'; ?>
