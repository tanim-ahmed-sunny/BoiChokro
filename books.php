<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : 'all';
$location = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

$sql = "SELECT b.*, u.phone as seller_phone 
        FROM books b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.user_id != $user_id AND b.status = 'approved'";

if ($type !== 'all') {
    $sql .= " AND b.listing_type = '$type'";
}

if ($category !== 'all') {
    $sql .= " AND b.category = '$category'";
}

if (!empty($location)) {
    $sql .= " AND (b.city LIKE '%$location%' OR b.area LIKE '%$location%')";
}

$sql .= " ORDER BY b.created_at DESC";
$result = $conn->query($sql);

$categoriesRes = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
?>

<div class="py-4" style="background-color: var(--bg-cream);">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="font-family: var(--font-serif); color: var(--text-dark);">Browse Books</h1>
                <p class="text-muted mb-0">Discover your next great read from our community.</p>
            </div>
            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'library'): ?>
                <a href="add_book.php" class="btn rounded-pill px-4 shadow-sm" style="background-color: var(--primary-green); color: white; border: none;"><i class="bi bi-plus-lg me-2"></i>Post a Book</a>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--border-soft) !important; background-color: var(--bg-white);">
            <div class="card-body p-3">
                <form action="books.php" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3" style="border-color: var(--border-soft);"><i class="bi bi-grid" style="color: var(--primary-green);"></i></span>
                            <select name="category" class="form-select border-start-0 rounded-end-pill px-3" style="border-color: var(--border-soft);">
                                <option value="all" <?php echo $category == 'all' ? 'selected' : ''; ?>>All Categories</option>
                                <?php while($cat = $categoriesRes->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3" style="border-color: var(--border-soft);"><i class="bi bi-geo-alt" style="color: var(--secondary-beige);"></i></span>
                            <input type="text" name="location" class="form-control border-start-0 rounded-end-pill px-3" placeholder="Search by City or Area..." value="<?php echo htmlspecialchars($location); ?>" style="border-color: var(--border-soft);">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3" style="border-color: var(--border-soft);"><i class="bi bi-tag" style="color: var(--primary-green);"></i></span>
                            <select name="type" class="form-select border-start-0 rounded-end-pill px-3" style="border-color: var(--border-soft);">
                                <option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="sale" <?php echo $type == 'sale' ? 'selected' : ''; ?>>Buy/Sell</option>
                                <option value="swap" <?php echo $type == 'swap' ? 'selected' : ''; ?>>Swap Only</option>
                                <option value="donation" <?php echo $type == 'donation' ? 'selected' : ''; ?>>Free/Donation</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn w-100 rounded-pill py-2" style="background-color: var(--primary-green); color: white; border: none;">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm hover-scale overflow-hidden">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($row['images'] ?: 'https://via.placeholder.com/300x400?text=No+Image'); ?>" class="card-img-top object-fit-cover" alt="<?php echo htmlspecialchars($row['title']); ?>" style="height: 300px;">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <?php if($row['listing_type'] == 'swap'): ?>
                                        <span class="badge shadow-sm rounded-pill px-3 py-2 fw-bold" style="background: var(--primary-green); color: white;" title="<?php echo htmlspecialchars($row['swap_interest']); ?>">
                                            <i class="bi bi-arrow-left-right me-1"></i>Swap
                                        </span>
                                    <?php elseif($row['listing_type'] == 'donation'): ?>
                                        <span class="badge shadow-sm rounded-pill px-3 py-2 fw-bold" style="background: var(--primary-green); color: white;">Free</span>
                                    <?php else: ?>
                                        <span class="badge bg-white shadow-sm rounded-pill px-3 py-2 fw-bold" style="color: var(--primary-green); border: 1px solid var(--primary-green);">৳<?php echo htmlspecialchars($row['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-truncate mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text text-muted small mb-3"><?php echo htmlspecialchars($row['author']); ?></p>
                                <button class="btn w-100 rounded-pill btn-sm" style="background-color: var(--primary-green); color: white; border: none;" 
                                        data-bs-toggle="offcanvas" 
                                        data-bs-target="#bookDetailsOffcanvas"
                                        data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                        data-author="<?php echo htmlspecialchars($row['author']); ?>"
                                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                        data-image="<?php echo htmlspecialchars($row['images'] ?: 'https://via.placeholder.com/300x400?text=No+Image'); ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                        data-condition="<?php echo htmlspecialchars($row['condition']); ?>"
                                        data-area="<?php echo htmlspecialchars($row['area']); ?>"
                                        data-city="<?php echo htmlspecialchars($row['city']); ?>"
                                        data-type="<?php echo htmlspecialchars($row['listing_type']); ?>"
                                        data-swap="<?php echo htmlspecialchars($row['swap_interest']); ?>"
                                        data-book-id="<?php echo $row['id']; ?>"
                                        data-user-id="<?php echo $row['user_id']; ?>"
                                        data-phone="<?php echo htmlspecialchars($row['seller_phone'] ?: 'No phone provided'); ?>"
                                        onclick="showBookDetails(this)">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-book text-muted display-1"></i>
                    <h3 class="mt-3 text-muted">No books currently listed.</h3>
                    <p>Be the first to list a book!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end rounded-start-4 border-0 shadow-lg" tabindex="-1" id="bookDetailsOffcanvas" aria-labelledby="bookDetailsLabel" style="width: 400px; border-left: 3px solid var(--primary-color) !important;">
    <div class="offcanvas-header border-bottom" style="background: var(--primary-light);">
        <h5 class="offcanvas-title fw-bold text-eco-dark" id="bookDetailsLabel">Book Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <img id="offcanvasImage" src="" class="w-100 object-fit-cover" style="height: 300px;" alt="Book Cover">
        <div class="p-4">
            <h3 class="fw-bold mb-1 text-eco-dark" id="offcanvasTitle"></h3>
            <p class="text-eco-muted mb-3" id="offcanvasAuthor"></p>
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div id="priceDisplay">
                    <span class="display-6 fw-bold" style="color: var(--primary-color);" id="offcanvasPriceContainer">
                        ৳<span id="offcanvasPrice"></span>
                    </span>
                    <div id="offcanvasSwap" class="d-none">
                        <small class="text-eco-muted d-block text-uppercase fw-bold" style="font-size: 0.8rem;">Wants to Swap For:</small>
                        <span class="fs-4 fw-bold" style="color: var(--primary-color);" id="offcanvasSwapText"></span>
                    </div>
                </div>
                <span class="eco-badge" id="offcanvasType"></span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="p-3 rounded-3 text-center" style="background: var(--primary-light);">
                        <i class="bi bi-geo-alt mb-2 fs-5" style="color: var(--primary-color);"></i>
                        <div class="small fw-bold text-eco-dark">Location</div>
                        <div class="small text-eco-muted" id="offcanvasLocation"></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 text-center" style="background: var(--secondary-light);">
                        <i class="bi bi-stars mb-2 fs-5" style="color: var(--secondary-color);"></i>
                        <div class="small fw-bold text-eco-dark">Condition</div>
                        <div class="small text-eco-muted" id="offcanvasCondition"></div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-2 text-eco-dark">Description</h6>
            <p class="text-secondary small mb-4" id="offcanvasDescription"></p>

            <div class="d-grid gap-2">
                <div id="sellerContactInfo" class="d-none animate__animated animate__fadeIn">
                    <div class="card rounded-4 mb-3" style="border: 2px solid var(--primary-color); background: var(--primary-light);">
                        <div class="card-body p-3 text-center">
                            <span class="text-eco-muted small d-block mb-1">Seller's Contact Number</span>
                            <h4 class="fw-bold mb-3" style="color: var(--primary-color);" id="displayPhoneNumber"></h4>
                            <div class="d-flex gap-2">
                                <a href="" id="callBtn" class="btn btn-eco-primary rounded-pill flex-grow-1">
                                    <i class="bi bi-telephone-fill me-2"></i>Call
                                </a>
                                <a href="" id="whatsappBtn" target="_blank" class="btn btn-eco-secondary rounded-pill flex-grow-1">
                                    <i class="bi bi-whatsapp me-2"></i>WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button id="contactSellerBtn" class="btn btn-eco-primary rounded-pill py-2" onclick="revealSellerContact()">
                    <i class="bi bi-person-badge me-2"></i>Contact Seller
                </button>
                <button id="swapInterestBtn" class="btn btn-eco-secondary rounded-pill py-2 d-none" data-bs-toggle="modal" data-bs-target="#swapInterestModal">
                    <i class="bi bi-arrow-left-right me-2"></i>Are You Interested?
                </button>
                <button class="btn btn-eco-outline rounded-pill py-2" onclick="addToWishlist()">
                    <i class="bi bi-heart me-2"></i>Add to Wishlist
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentBookId = null;
let currentBookType = null;
let currentSellerPhone = null;

function showBookDetails(button) {
    currentBookId = button.dataset.bookId;
    currentBookType = button.dataset.type;
    currentSellerPhone = button.dataset.phone;
    
    document.getElementById('offcanvasTitle').textContent = button.dataset.title;
    document.getElementById('offcanvasAuthor').textContent = button.dataset.author;
    document.getElementById('offcanvasPrice').textContent = button.dataset.price;
    document.getElementById('offcanvasImage').src = button.dataset.image;
    document.getElementById('offcanvasDescription').textContent = button.dataset.description || 'No description available.';
    document.getElementById('offcanvasCondition').textContent = button.dataset.condition;
    document.getElementById('offcanvasLocation').textContent = button.dataset.area + ', ' + button.dataset.city;
    document.getElementById('offcanvasType').textContent = button.dataset.type.charAt(0).toUpperCase() + button.dataset.type.slice(1);
    
    document.getElementById('sellerContactInfo').classList.add('d-none');
    document.getElementById('contactSellerBtn').classList.remove('d-none');
    
    const priceContainer = document.getElementById('offcanvasPriceContainer');
    const swapContainer = document.getElementById('offcanvasSwap');
    
    if (button.dataset.type === 'swap') {
        priceContainer.classList.add('d-none');
        swapContainer.classList.remove('d-none');
        document.getElementById('offcanvasSwapText').textContent = button.dataset.swap || 'Open to offers';
    } else if (button.dataset.type === 'donation') {
        priceContainer.classList.remove('d-none');
        swapContainer.classList.add('d-none');
        document.getElementById('offcanvasPrice').textContent = '0.00 (Free)';
    } else {
        priceContainer.classList.remove('d-none');
        swapContainer.classList.add('d-none');
        document.getElementById('offcanvasPrice').textContent = button.dataset.price;
    }
    
    const contactBtn = document.getElementById('contactSellerBtn');
    const swapBtn = document.getElementById('swapInterestBtn');
    
    if (button.dataset.type === 'swap') {
        contactBtn.classList.add('d-none');
        swapBtn.classList.remove('d-none');
    } else {
        contactBtn.classList.remove('d-none');
        swapBtn.classList.add('d-none');
    }
}

function revealSellerContact() {
    if (!currentSellerPhone || currentSellerPhone === 'No phone provided') {
        alert('Seller has not provided a contact number.');
        return;
    }
    
    document.getElementById('displayPhoneNumber').textContent = currentSellerPhone;
    document.getElementById('callBtn').href = 'tel:' + currentSellerPhone;
    document.getElementById('whatsappBtn').href = 'https://wa.me/' + currentSellerPhone.replace(/\D/gs, '');
    
    document.getElementById('sellerContactInfo').classList.remove('d-none');
    document.getElementById('contactSellerBtn').classList.add('d-none');
}

function addToWishlist() {
    const title = document.getElementById('offcanvasTitle').textContent;
    const author = document.getElementById('offcanvasAuthor').textContent;
    
    const formData = new FormData();
    formData.append('title', title);
    formData.append('author', author);
    
    fetch('backend/actions/add_to_wishlist.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Added to your wishlist! We will notify you when more copies are available.');
        } else {
            alert(data.error || 'Failed to add to wishlist');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error connecting to server');
    });
}
</script>

<?php include 'swap_modal.php'; ?>
<?php require_once 'backend/includes/footer.php'; ?>
