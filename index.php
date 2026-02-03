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
<section class="py-5" style="background-color: var(--bg-cream);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-3" style="font-family: var(--font-serif); color: var(--text-dark);">
                    Read. Reuse. <span style="color: var(--text-dark);">Reduce Waste.</span>
                </h1>
                <p class="lead mb-4" style="color: var(--text-muted); font-size: 1.1rem;">
                    Buy, swap, or donate used books —<br>
                    <em style="font-style: italic;">and give knowledge a second life.</em>
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="books.php?type=sale" class="btn px-4 py-3 rounded-pill fw-semibold" style="background-color: var(--primary-green); color: white; border: none;">
                        Buy Used Books
                    </a>
                    <a href="books.php?type=swap" class="btn px-4 py-3 rounded-pill fw-semibold" style="background-color: var(--primary-green); color: white; border: none;">
                        Swap a Book
                    </a>
                    <a href="donate.php" class="btn px-4 py-3 rounded-pill fw-semibold" style="background-color: var(--secondary-beige); color: white; border: none;">
                        Donate Books
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="images/hero-illustration.png" alt="People exchanging books" class="img-fluid" style="max-width: 500px;">
            </div>
        </div>
    </div>
</section>

<!-- Environmental Impact Section -->
<section class="py-5" style="background-color: var(--bg-cream);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2" style="font-family: var(--font-serif); color: var(--text-dark); font-size: 2rem;">
                Our Environmental Impact
            </h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4" style="background-color: var(--bg-white); border-radius: 1.5rem;">
                    <div class="mb-3">
                        <img src="images/books-saved.png" alt="Books Saved" style="width: 120px; height: 120px; object-fit: contain;">
                    </div>
                    <h5 class="fw-bold mb-2" style="font-family: var(--font-serif); color: var(--text-dark);">Books Saved</h5>
                    <p class="text-muted small mb-0">Every reused book saves paper, water, and energy.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4" style="background-color: var(--bg-white); border-radius: 1.5rem;">
                    <div class="mb-3">
                        <img src="images/tree.png" alt="Trees Protected" style="width: 120px; height: 120px; object-fit: contain;">
                    </div>
                    <h5 class="fw-bold mb-2" style="font-family: var(--font-serif); color: var(--text-dark);">Trees Protected</h5>
                    <p class="text-muted small mb-0">Less new paper means more number of trees saved.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center p-4" style="background-color: var(--bg-white); border-radius: 1.5rem;">
                    <div class="mb-3">
                        <img src="images/circular-reading.png" alt="Circular Reading" style="width: 120px; height: 120px; object-fit: contain;">
                    </div>
                    <h5 class="fw-bold mb-2" style="font-family: var(--font-serif); color: var(--text-dark);">Circular Reading</h5>
                    <p class="text-muted small mb-0">Keeping books out of landfills and in readers' hands.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Books -->
<section class="py-5 bg-eco-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-1 text-eco-dark">Recently Listed Books</h2>
                <p class="text-eco-muted">Discover the latest additions to our eco-friendly community.</p>
            </div>
            <a href="books.php" class="text-eco-primary fw-bold text-decoration-none mb-2">View All <i class="bi bi-arrow-right"></i></a>
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
<section class="py-5" style="background-color: var(--bg-cream);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2" style="font-family: var(--font-serif); color: var(--text-dark); font-size: 2rem;">
                How It Works
            </h2>
        </div>
        <div class="row g-5 justify-content-center">
            <div class="col-md-4 text-center">
                <div class="mb-4">
                    <img src="images/find-book.png" alt="Find a Book" style="width: 100px; height: 100px; object-fit: contain;">
                </div>
                <h5 class="fw-bold mb-3" style="font-family: var(--font-serif); color: var(--text-dark);">Find a Book</h5>
            </div>
            <div class="col-md-4 text-center">
                <div class="mb-4">
                    <img src="images/swap-book.png" alt="Choose Buy, Swap, or Donate" style="width: 100px; height: 100px; object-fit: contain;">
                </div>
                <h5 class="fw-bold mb-3" style="font-family: var(--font-serif); color: var(--text-dark);">Choose Buy, Swap, or Donate</h5>
            </div>
            <div class="col-md-4 text-center">
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; margin: 0 auto; background-color: var(--primary-light); border-radius: 50%;">
                        <i class="bi bi-book-half" style="font-size: 3rem; color: var(--primary-green);"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-3" style="font-family: var(--font-serif); color: var(--text-dark);">Share the Story</h5>
            </div>
        </div>
    </div>
</section>

<?php require_once 'backend/includes/footer.php'; ?>
