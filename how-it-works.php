<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 
?>

<div class="bg-eco-light py-5">
    <div class="container">
        <!-- Hero Section -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-eco-dark mb-3">
                <i class="bi bi-info-circle-fill me-3" style="color: var(--primary-color);"></i>
                How Boichokro Works
            </h1>
            <p class="lead text-eco-muted" style="max-width: 700px; margin: 0 auto;">
                Join Bangladesh's eco-friendly book community and make a positive impact on our environment, one book at a time.
            </p>
        </div>

        <!-- Environmental Mission -->
        <div class="impact-section mb-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="fw-bold mb-3" style="color: var(--primary-dark);">
                        <i class="bi bi-leaf me-2"></i>Our Environmental Mission
                    </h2>
                    <p class="text-eco-muted mb-3">
                        Every year, millions of books end up in landfills, wasting paper and contributing to deforestation. Boichokro is changing that by creating a sustainable ecosystem for book lovers.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: var(--primary-color);"></i> Reduce paper waste and save trees</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: var(--primary-color);"></i> Lower carbon emissions from book production</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: var(--primary-color);"></i> Make reading accessible and affordable</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: var(--primary-color);"></i> Build a sustainable reading community</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="impact-card">
                                <div class="impact-icon"><i class="bi bi-tree-fill"></i></div>
                                <span class="impact-number">1</span>
                                <div class="impact-label">Tree Saved</div>
                                <p class="text-muted small mt-2 mb-0">Per 20 books reused</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="impact-card">
                                <div class="impact-icon"><i class="bi bi-droplet-fill"></i></div>
                                <span class="impact-number">7,000L</span>
                                <div class="impact-label">Water Saved</div>
                                <p class="text-muted small mt-2 mb-0">Per tree preserved</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How to Use Boichokro -->
        <div class="mb-5">
            <h2 class="text-center fw-bold mb-5 text-eco-dark">Simple Steps to Get Started</h2>
            
            <!-- Buy Used Books -->
            <div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid var(--primary-color) !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="impact-icon mx-auto">
                                <i class="bi bi-book"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h3 class="fw-bold mb-2 text-eco-dark">
                                <span class="badge rounded-pill me-2" style="background: var(--primary-color); color: white;">1</span>
                                Buy Used Books
                            </h3>
                            <p class="text-eco-muted mb-3">Browse our collection of pre-loved books at affordable prices. Every purchase supports sustainability.</p>
                            <ol class="text-eco-muted">
                                <li>Visit the <a href="books.php?type=sale" class="text-eco-primary fw-bold">Browse Books</a> page</li>
                                <li>Filter by category, location, or type (Buy/Sell)</li>
                                <li>Click "View Details" to see book information</li>
                                <li>Contact the seller directly via phone or WhatsApp</li>
                                <li>Arrange pickup or delivery</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Swap Books -->
            <div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid var(--secondary-color) !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="impact-icon mx-auto" style="background: var(--secondary-light);">
                                <i class="bi bi-arrow-left-right" style="color: var(--secondary-color);"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h3 class="fw-bold mb-2 text-eco-dark">
                                <span class="badge rounded-pill me-2" style="background: var(--secondary-color); color: white;">2</span>
                                Swap a Book
                            </h3>
                            <p class="text-eco-muted mb-3">Exchange books you've finished for new reads. The most eco-friendly way to discover new stories!</p>
                            <ol class="text-eco-muted">
                                <li>Find a book marked as "Swap Only"</li>
                                <li>Click "Are You Interested?" button</li>
                                <li>Select a book from your collection to offer in exchange</li>
                                <li>Wait for the owner to accept your swap request</li>
                                <li>Once accepted, coordinate the exchange via phone</li>
                                <li>Meet up and swap your books!</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donate Books -->
            <div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid var(--accent-sage) !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="impact-icon mx-auto" style="background: var(--primary-light);">
                                <i class="bi bi-gift" style="color: var(--accent-sage);"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h3 class="fw-bold mb-2 text-eco-dark">
                                <span class="badge rounded-pill me-2" style="background: var(--accent-sage); color: white;">3</span>
                                Donate Books
                            </h3>
                            <p class="text-eco-muted mb-3">Give your books a second life by donating to readers in need. Spread knowledge and reduce waste.</p>
                            <ol class="text-eco-muted">
                                <li>Easy Donor Application</li>
                                <li>Book Details Transparency</li>
                                <li>Purpose-Driven Donation</li>
                                <li>Efficient Coordination</li>
                                <li>Community Impact!</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Your Books -->
            <div class="card mb-4 border-0 shadow-sm" style="border-left: 4px solid var(--primary-dark) !important;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="impact-icon mx-auto" style="background: var(--primary-light);">
                                <i class="bi bi-plus-lg" style="color: var(--primary-dark);"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h3 class="fw-bold mb-2 text-eco-dark">
                                <span class="badge rounded-pill me-2" style="background: var(--primary-dark); color: white;">4</span>
                                List Your Books
                            </h3>
                            <p class="text-eco-muted mb-3">Have books you've finished? List them on Boichokro and give them a new home.</p>
                            <ol class="text-eco-muted">
                                <li>Click "Post a Book" in the navigation menu</li>
                                <li>Fill in book details (title, author, condition, etc.)</li>
                                <li>Upload a clear photo of your book</li>
                                <li>Choose listing type: Sell, Swap, or Donate</li>
                                <li>Submit for admin approval</li>
                                <li>Once approved, your book will be visible to the community!</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Community Features -->
        <div class="impact-section mb-5">
            <h2 class="text-center fw-bold mb-4" style="color: var(--primary-dark);">
                <i class="bi bi-people-fill me-2"></i>Join Our Community
            </h2>
            <p class="text-center text-eco-muted mb-5">Connect with fellow book lovers, share recommendations, and discuss your favorite reads.</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-eco-dark">Discussions</h5>
                        <p class="text-eco-muted">Start threads, share book reviews, and engage in meaningful conversations.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-eco-dark">Wishlist</h5>
                        <p class="text-eco-muted">Add books to your wishlist and get notified when they become available.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-building"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-eco-dark">Libraries</h5>
                        <p class="text-eco-muted">Borrow books from partner libraries and support local reading spaces.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center py-5">
            <h2 class="fw-bold mb-3 text-eco-dark">Ready to Make a Difference?</h2>
            <p class="lead text-eco-muted mb-4">Join thousands of readers reducing waste and saving trees.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-eco-primary px-5 py-3">
                        <i class="bi bi-person-plus me-2"></i>Create Free Account
                    </a>
                    <a href="books.php" class="btn btn-eco-outline px-5 py-3">
                        <i class="bi bi-book me-2"></i>Browse Books
                    </a>
                <?php else: ?>
                    <a href="add_book.php" class="btn btn-eco-primary px-5 py-3">
                        <i class="bi bi-plus-lg me-2"></i>List Your First Book
                    </a>
                    <a href="books.php" class="btn btn-eco-secondary px-5 py-3">
                        <i class="bi bi-search me-2"></i>Find Books to Swap
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'backend/includes/footer.php'; ?>
