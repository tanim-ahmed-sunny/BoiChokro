<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch user's books
$myBooks = $conn->query("SELECT * FROM books WHERE user_id = $user_id ORDER BY created_at DESC");

// Fetch active swap requests (where I am requester or owner)
$swapRequests = $conn->query("
    SELECT sr.*, rb.title as requested_title, ob.title as offered_title,
           u_req.username as requester_name, u_own.username as owner_name,
           u_req.phone as requester_phone, u_own.phone as owner_phone
    FROM swap_requests sr
    JOIN books rb ON sr.requested_book_id = rb.id
    JOIN books ob ON sr.offered_book_id = ob.id
    JOIN users u_req ON sr.requester_id = u_req.id
    JOIN users u_own ON sr.owner_id = u_own.id
    WHERE sr.requester_id = $user_id OR sr.owner_id = $user_id
    ORDER BY sr.created_at DESC
");

// Fetch wishlist
$wishlist = $conn->query("SELECT * FROM wishlist WHERE user_id = $user_id ORDER BY created_at DESC");

// IMPACT DATA REMOVED

// Fetch borrowed books & requests (using LEFT JOIN for robustness)
$borrowedBooks = $conn->query("
    SELECT lb.*, b.title as book_title, l.library_name 
    FROM library_bookings lb 
    LEFT JOIN library_books b ON lb.library_book_id = b.id 
    LEFT JOIN libraries l ON lb.library_id = l.id 
    WHERE lb.user_id = $user_id 
    ORDER BY lb.created_at DESC
");

// Path helper for profile images
$profile_image = $user['profile_image'];
if ($profile_image && strpos($profile_image, 'http') !== 0 && strpos($profile_image, 'backend/') !== 0) {
    $profile_image = 'backend/' . ltrim($profile_image, '/');
}
?>

<div class="bg-light py-5">
    <div class="container">
        <!-- Feedback Alerts -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Sidebar: Profile Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 text-center p-4 mb-4">
                    <div class="position-relative mx-auto mb-3" style="width: 120px; height: 120px;">
                        <div class="rounded-circle overflow-hidden w-100 h-100 border border-4 border-white shadow-sm bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                            <?php if ($profile_image): ?>
                                <img src="<?php echo htmlspecialchars($profile_image); ?>" class="w-100 h-100 object-fit-cover">
                            <?php else: ?>
                                <i class="bi bi-person-fill text-primary display-4"></i>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow-sm" title="Change Profile Picture">
                            <i class="bi bi-camera-fill"></i>
                        </button>
                    </div>
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                    <p class="text-muted small mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-emerald-50 text-emerald-600 rounded-pill px-3 py-2 border border-emerald-100">
                            <i class="bi bi-envelope-check me-1"></i> Verified
                        </span>
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2 border">
                            <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($user['area'] ?: 'General'); ?>
                        </span>
                    </div>
                    <hr class="my-4">
                    <div class="row text-center g-0">
                        <div class="col-6 border-end">
                            <div class="fw-bold"><?php echo $myBooks->num_rows; ?></div>
                            <small class="text-muted">Books</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold"><?php echo $wishlist->num_rows; ?></div>
                            <small class="text-muted">Wishlist</small>
                        </div>
                    </div>
                </div>

                <!-- Profile Details Shortcut Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Contact Info</h5>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="text-muted"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></div>
                        <div class="text-muted"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($user['phone'] ?: 'No phone provided'); ?></div>
                        <div class="text-muted"><i class="bi bi-house-door me-2"></i><?php echo htmlspecialchars($user['address'] ?: 'No address provided'); ?></div>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill mt-3 fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square me-2"></i>Update Details
                    </button>
                </div>
            </div>

            <!-- Right Content: Tabs -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 p-0">
                        <ul class="nav nav-tabs nav-fill border-0" id="profileTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3 fw-bold" id="my-books-tab" data-bs-toggle="tab" data-bs-target="#my-books">My Books</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3 fw-bold" id="swap-requests-tab" data-bs-toggle="tab" data-bs-target="#swap-requests">Swap Requests</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3 fw-bold" id="wishlist-tab" data-bs-toggle="tab" data-bs-target="#wishlist">Wishlist</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-3 fw-bold" id="borrowed-books-tab" data-bs-toggle="tab" data-bs-target="#borrowed-books">Borrowed Books</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="profileTabsContent">
                            <!-- My Books Tab -->
                            <div class="tab-pane fade show active" id="my-books">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">Listings</h5>
                                    <a href="add_book.php" class="btn btn-primary btn-sm rounded-pill"><i class="bi bi-plus-lg me-1"></i>New Listing</a>
                                </div>
                                <div class="row g-3">
                                    <?php if ($myBooks->num_rows > 0): ?>
                                        <?php while($book = $myBooks->fetch_assoc()): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card h-100 border shadow-none rounded-3">
                                                    <div class="position-relative">
                                                        <img src="<?php echo htmlspecialchars($book['images'] ?: 'https://via.placeholder.com/300x400?text=No+Image'); ?>" class="card-img-top object-fit-cover" style="height: 180px;">
                                                        <span class="badge bg-primary rounded-pill position-absolute top-0 end-0 m-2 shadow-sm">
                                                            <?php echo ucfirst($book['listing_type']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        <h6 class="fw-bold text-truncate mb-3"><?php echo htmlspecialchars($book['title']); ?></h6>
                                                        <div class="d-flex gap-2">
                                                            <a href="backend/actions/delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-danger btn-sm w-100 py-2" onclick="return confirm('Are you sure you want to delete this listing?');"><i class="bi bi-trash me-1"></i>Delete Listing</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-journal-plus text-muted display-4"></i>
                                            <p class="text-muted mt-3">You haven't listed any books yet.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Swap Requests Tab -->
                            <div class="tab-pane fade" id="swap-requests">
                                <h5 class="fw-bold mb-4">Pending Swaps</h5>
                                <?php if ($swapRequests->num_rows > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php while($sr = $swapRequests->fetch_assoc()): ?>
                                            <div class="list-group-item border-0 px-0 mb-3 bg-light rounded-4 p-4 border" id="swap-req-<?php echo $sr['id']; ?>">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="badge <?php echo $sr['status'] == 'pending' ? 'bg-warning' : ($sr['status'] == 'accepted' ? 'bg-success' : 'bg-danger'); ?> rounded-pill px-3">
                                                        <?php echo ucfirst($sr['status']); ?>
                                                    </span>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($sr['created_at'])); ?></small>
                                                </div>
                                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                                                    <div class="flex-grow-1">
                                                        <?php if ($sr['requester_id'] == $user_id): ?>
                                                            <div class="mb-2"><span class="text-muted small">You requested:</span> <strong class="ms-1"><?php echo htmlspecialchars($sr['requested_title']); ?></strong></div>
                                                            <div><span class="text-muted small">You offered:</span> <strong class="ms-1 text-primary"><?php echo htmlspecialchars($sr['offered_title']); ?></strong></div>
                                                        <?php else: ?>
                                                            <div class="mb-2"><span class="fw-bold">@<?php echo htmlspecialchars($sr['requester_name']); ?></span> <span class="text-muted small ms-1">wants:</span> <strong class="ms-1 text-primary"><?php echo htmlspecialchars($sr['requested_title']); ?></strong></div>
                                                            <div><span class="text-muted small">Offers you:</span> <strong class="ms-1"><?php echo htmlspecialchars($sr['offered_title']); ?></strong></div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($sr['status'] == 'pending' && $sr['owner_id'] == $user_id): ?>
                                                        <div class="d-flex gap-2">
                                                            <button onclick="handleSwap(<?php echo $sr['id']; ?>, 'accept_swap')" class="btn btn-success rounded-pill px-4 shadow-sm">Accept</button>
                                                            <button onclick="handleSwap(<?php echo $sr['id']; ?>, 'reject_swap')" class="btn btn-outline-danger rounded-pill px-4">Decline</button>
                                                        </div>
                                                    <?php elseif ($sr['status'] == 'accepted'): ?>
                                                        <div class="bg-white p-3 rounded-3 border flex-grow-1 mt-3 mt-md-0">
                                                            <div class="small fw-bold text-muted mb-2 text-uppercase"><i class="bi bi-person-badge me-2"></i>Contact Information</div>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="fw-bold"><i class="bi bi-telephone me-2 text-primary"></i><?php echo htmlspecialchars($sr['requester_id'] == $user_id ? $sr['owner_phone'] : $sr['requester_phone']); ?></div>
                                                                <div class="vr"></div>
                                                                <div class="text-muted small">Call to coordinate pickup/meeting.</div>
                                                            </div>
                                                            
                                                            <hr class="my-2">
                                                            
                                                            <div class="mt-2">
                                                                <div class="text-secondary small mb-2">Did you already exchange your books?</div>
                                                                <?php 
                                                                $my_confirmed = ($sr['requester_id'] == $user_id) ? $sr['requester_confirmed'] : $sr['owner_confirmed'];
                                                                if ($my_confirmed): ?>
                                                                    <button class="btn btn-success btn-sm rounded-pill w-100 disabled"><i class="bi bi-check2-all me-1"></i> Thank you for your confirmation</button>
                                                                <?php else: ?>
                                                                    <button onclick="confirmExchange(<?php echo $sr['id']; ?>)" class="btn btn-primary btn-sm rounded-pill w-100">Yes, Exchanged!</button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-arrow-left-right text-muted display-4"></i>
                                        <p class="text-muted mt-3">No active swap requests.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Wishlist Tab -->
                            <div class="tab-pane fade" id="wishlist">
                                <h5 class="fw-bold mb-4">Books I'm looking for</h5>
                                <?php if ($wishlist->num_rows > 0): ?>
                                    <div class="row g-3">
                                        <?php while($item = $wishlist->fetch_assoc()): ?>
                                            <div class="col-md-6">
                                                <div class="card h-100 border shadow-none rounded-4 p-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($item['author']); ?></p>
                                                            <?php if($item['notified']): ?>
                                                                <span class="badge bg-success-subtle text-success small">Found! Check Email</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted small">Watching...</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <button class="btn btn-link text-danger p-0 border-0"><i class="bi bi-x-circle-fill"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-heart text-muted display-4"></i>
                                        <p class="text-muted mt-3">Your wishlist is empty.</p>
                                        <a href="books.php" class="btn btn-outline-primary btn-sm rounded-pill">Find Books</a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Borrowed Books Tab -->
                            <div class="tab-pane fade" id="borrowed-books">
                                <h5 class="fw-bold mb-4">Books from Libraries</h5>
                                <?php if ($borrowedBooks->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle border-0">
                                            <thead>
                                                <tr class="text-muted small text-uppercase">
                                                    <th class="border-0">Book Title</th>
                                                    <th class="border-0">Library</th>
                                                    <th class="border-0">Return Date</th>
                                                    <th class="border-0">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($bb = $borrowedBooks->fetch_assoc()): ?>
                                                    <tr>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($bb['book_title']); ?></td>
                                                        <td><i class="bi bi-building me-1 text-primary"></i> <?php echo htmlspecialchars($bb['library_name']); ?></td>
                                                        <td><?php echo $bb['return_date'] ? date('M d, Y', strtotime($bb['return_date'])) : 'Pending'; ?></td>
                                                        <td>
                                                            <?php 
                                                            $statusClass = 'bg-light text-dark';
                                                            if($bb['status'] == 'issued') $statusClass = 'bg-primary-subtle text-primary';
                                                            if($bb['status'] == 'returned') $statusClass = 'bg-success-subtle text-success';
                                                            if($bb['status'] == 'overdue') $statusClass = 'bg-danger-subtle text-danger';
                                                            ?>
                                                            <span class="badge <?php echo $statusClass; ?> rounded-pill px-3">
                                                                <?php echo ucfirst($bb['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-book-half text-muted display-4"></i>
                                        <p class="text-muted mt-3">You haven't borrowed any books from libraries yet.</p>
                                        <a href="libraries.php" class="btn btn-outline-primary btn-sm rounded-pill">Explore Libraries</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Update Profile Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <form action="backend/actions/update_profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" name="full_name" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="017XXXXXXXX">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Street Address</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-house"></i></span>
                                <input type="text" name="address" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($user['address']); ?>" placeholder="House #, Road #, etc.">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">City</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-building"></i></span>
                                <input type="text" name="city" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Area</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="area" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($user['area']); ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Profile Picture</label>
                            <div class="bg-light p-4 rounded-4 border-dashed text-center">
                                <div class="mb-3">
                                    <?php if ($profile_image): ?>
                                        <img src="<?php echo htmlspecialchars($profile_image); ?>" class="rounded-circle shadow-sm" width="80" height="80" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="bi bi-person-fill fs-2"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="profile_image" class="form-control bg-white border-0 py-2" accept="image/*">
                                <div class="form-text mt-2">Recommended: Square image, max 2MB.</div>
                            </div>
                        </div>
                        <div class="col-12 mt-5">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                                <i class="bi bi-check2-circle me-2"></i>Save Final Changes
                            </button>
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

<script>
function handleSwap(requestId, action) {
    if (!confirm('Are you sure you want to ' + (action === 'accept_swap' ? 'accept' : 'decline') + ' this swap request?')) return;
    
    const formData = new FormData();
    formData.append('action', action);
    formData.append('request_id', requestId);

    fetch('backend/actions/swap_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

function confirmExchange(requestId) {
    if (!confirm('Are you sure the exchange is complete? Both books will be removed from system once both parties confirm.')) return;
    
    const formData = new FormData();
    formData.append('action', 'confirm_exchange');
    formData.append('request_id', requestId);

    fetch('backend/actions/swap_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}
</script>
