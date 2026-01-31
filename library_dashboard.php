<?php
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'library') {
   echo "<script>window.location.href='login.php';</script>";
   exit;
}

$user_id = $_SESSION['user_id'];

// Get Library ID
$stmt = $conn->prepare("SELECT * FROM libraries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$lib_result = $stmt->get_result();
$library = $lib_result->fetch_assoc();

if (!$library) {
    // Placeholder if they haven't set up library details yet
    echo '<div class="container py-5"><div class="alert alert-warning">Please complete your library profile setup.</div></div>';
    require_once 'backend/includes/footer.php';
    exit;
}

$library_id = $library['id'];

// Fetch Stats
$stats = [
    'total_books' => 0,
    'issued_books' => 0,
    'overdue_books' => 0,
    'pending_requests' => 0
];

$res = $conn->query("SELECT COUNT(*) as c FROM library_books WHERE library_id = $library_id");
if($res) $stats['total_books'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) as c FROM library_bookings WHERE library_id = $library_id AND status = 'issued'");
if($res) $stats['issued_books'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) as c FROM library_bookings WHERE library_id = $library_id AND status = 'issued' AND return_date < CURDATE()");
if($res) $stats['overdue_books'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) as c FROM library_bookings WHERE library_id = $library_id AND status = 'pending'");
if($res) $stats['pending_requests'] = $res->fetch_assoc()['c'];

// Fetch Inventory
$inventory = $conn->query("SELECT * FROM library_books WHERE library_id = $library_id ORDER BY created_at DESC");

// Fetch Active Loans
$active_loans_sql = "SELECT b.*, lb.title, u.full_name, u.email 
                     FROM library_bookings b 
                     JOIN library_books lb ON b.library_book_id = lb.id 
                     JOIN users u ON b.user_id = u.id 
                     WHERE b.library_id = $library_id AND b.status = 'issued'";
$active_loans = $conn->query($active_loans_sql);

// Fetch History
$history_sql = "SELECT b.*, lb.title, u.full_name 
                FROM library_bookings b 
                JOIN library_books lb ON b.library_book_id = lb.id 
                JOIN users u ON b.user_id = u.id 
                WHERE b.library_id = $library_id AND b.status != 'issued' 
                ORDER BY b.updated_at DESC LIMIT 50";
$history = $conn->query($history_sql);

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <img src="<?php echo htmlspecialchars($library['image_url'] ?: 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&q=80&w=800'); ?>" class="rounded-4 shadow-sm" width="80" height="80" style="object-fit: cover;">
            <div>
                <span class="badge bg-emerald-100 text-emerald-700 mb-1">Library Dashboard</span>
                <h1 class="fw-bold mb-0"><?php echo htmlspecialchars($library['library_name']); ?></h1>
                <p class="text-muted mb-0 small"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($library['city'] . ', ' . $library['area']); ?></p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="library_profile.php?id=<?php echo (int)$library_id; ?>" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-eye me-2"></i>Public Preview
            </a>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBookModal">
                <i class="bi bi-plus-lg me-2"></i>Add New Book
            </button>
        </div>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-book fs-4 text-white"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-25 text-white">Total Inventory</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-0"><?php echo (int)$stats['total_books']; ?></h2>
                    <p class="mb-0 small opacity-75">Books in collection</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-emerald-500 text-white h-100" style="background-color: #10b981;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-journal-check fs-4 text-white"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-25 text-white">Active Loans</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $stats['issued_books']; ?></h2>
                    <p class="mb-0 small opacity-75">Books currently lent out</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-rose-500 text-white h-100" style="background-color: #f43f5e;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-exclamation-octagon fs-4 text-white"></i>
                        </div>
                        <span class="badge bg-white bg-opacity-25 text-white">Overdue</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-0"><?php echo (int)$stats['overdue_books']; ?></h2>
                    <p class="mb-0 small opacity-75">Books past return date</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($stats['pending_requests'] > 0): ?>
    <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center justify-content-between p-4">
        <div class="d-flex align-items-center">
            <div class="bg-warning bg-opacity-25 rounded-circle p-3 me-3 text-warning-emphasis">
                <i class="bi bi-bell-fill fs-4"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark"><?php echo (int)$stats['pending_requests']; ?> Pending Requests</h4>
                <p class="mb-0 text-muted">Users are waiting for your approval.</p>
            </div>
        </div>
        <button class="btn btn-warning rounded-pill px-4 fw-bold" onclick="document.getElementById('requests-tab').click()">Review Requests</button>
    </div>
    <?php endif; ?>

    <!-- Main Content Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab">
                Requests 
                <?php 
                $pending_count_res = $conn->query("SELECT COUNT(*) as c FROM library_bookings WHERE library_id = $library_id AND status = 'pending'");
                $pending_count = $pending_count_res ? $pending_count_res->fetch_assoc()['c'] : 0;
                if($pending_count > 0): 
                ?>
                <span class="badge bg-danger rounded-pill ms-2"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab">Active Loans</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">Inventory</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">History</button>
        </li>
    </ul>

    <div class="tab-content" id="dashboardTabsContent">
        
        <!-- Requests Tab -->
        <div class="tab-pane fade show active" id="requests" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                 <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4">
                    <h5 class="fw-bold mb-0">Borrow Requests</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Book</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Requested</th>
                                <th class="px-4 py-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $requests_sql = "SELECT b.*, lb.title, lb.available_copies, u.full_name, u.email 
                                            FROM library_bookings b 
                                            JOIN library_books lb ON b.library_book_id = lb.id 
                                            JOIN users u ON b.user_id = u.id 
                                            WHERE b.library_id = $library_id AND b.status = 'pending' 
                                            ORDER BY b.created_at ASC";
                            $requests = $conn->query($requests_sql);
                            
                            if ($requests && $requests->num_rows > 0): 
                                while($req = $requests->fetch_assoc()):
                            ?>
                                <tr>
                                    <td class="px-4 fw-medium">
                                        <?php echo htmlspecialchars($req['title']); ?>
                                        <?php if($req['available_copies'] < 1): ?>
                                            <span class="badge bg-danger-subtle text-danger ms-2">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4">
                                        <div><?php echo htmlspecialchars($req['full_name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($req['email']); ?></div>
                                    </td>
                                    <td class="px-4 text-muted small"><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                    <td class="px-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <form action="backend/actions/library_actions.php" method="POST">
                                                <input type="hidden" name="action" value="reject_request">
                                                <input type="hidden" name="booking_id" value="<?php echo $req['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Reject</button>
                                            </form>
                                            
                                            <?php if($req['available_copies'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" 
                                                    onclick="approveRequest('<?php echo $req['id']; ?>', '<?php echo addslashes($req['title']); ?>', '<?php echo addslashes($req['email']); ?>')">
                                                Approve
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No pending requests.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lending Management Tab -->
        <div class="tab-pane fade" id="loans" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Active Loans</h5>
                    <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#lendBookModal">
                        <i class="bi bi-arrow-right-circle me-2"></i>New Loan
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Book Title</th>
                                <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Borrower</th>
                                <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Issue Date</th>
                                <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Due Date</th>
                                <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Status</th>
                                <th class="px-4 py-3 text-secondary small fw-bold text-uppercase text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($active_loans->num_rows > 0): ?>
                                <?php while($loan = $active_loans->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-4 fw-medium"><?php echo htmlspecialchars($loan['title']); ?></td>
                                        <td class="px-4">
                                            <div><?php echo htmlspecialchars($loan['full_name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($loan['email']); ?></div>
                                        </td>
                                        <td class="px-4"><?php echo date('M d, Y', strtotime($loan['request_date'])); ?></td>
                                        <td class="px-4">
                                            <?php 
                                            $due = strtotime($loan['return_date']);
                                            $is_overdue = time() > $due;
                                            ?>
                                            <span class="<?php echo $is_overdue ? 'text-danger fw-bold' : ''; ?>">
                                                <?php echo date('M d, Y', $due); ?>
                                            </span>
                                        </td>
                                        <td class="px-4">
                                            <?php if($is_overdue): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">On Time</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 text-end">
                                            <form action="backend/actions/library_actions.php" method="POST" onsubmit="return confirm('Confirm return of this book?');">
                                                <input type="hidden" name="action" value="return_book">
                                                <input type="hidden" name="booking_id" value="<?php echo $loan['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                                    Mark Returned
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No active loans found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Inventory Tab -->
        <div class="tab-pane fade" id="inventory" role="tabpanel">
            <div class="row g-4">
                <?php while($book = $inventory->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5 class="fw-bold mb-0 text-truncate"><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($book['category']); ?></span>
                                </div>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($book['author']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-3 mb-3">
                                    <div class="text-center">
                                        <div class="small fw-bold text-secondary">Total</div>
                                        <div class="h5 mb-0"><?php echo $book['total_copies']; ?></div>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="text-center">
                                        <div class="small fw-bold text-secondary">Available</div>
                                        <div class="h5 mb-0 text-success"><?php echo $book['available_copies']; ?></div>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="text-center">
                                        <div class="small fw-bold text-secondary">ISBN</div>
                                        <div class="small mb-0 font-monospace"><?php echo $book['isbn'] ?: 'N/A'; ?></div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button class="btn btn-outline-primary rounded-pill btn-sm"
                                            onclick="fillLendModal('<?php echo $book['id']; ?>', '<?php echo addslashes($book['title']); ?>')">
                                        Lend This Book
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php if ($inventory->num_rows == 0): ?>
                    <div class="col-12 text-center py-5">
                       <i class="bi bi-journal-plus display-1 text-muted opacity-25"></i>
                       <p class="mt-3 text-muted">Your library inventory is empty. Start by adding books.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- History Tab -->
        <div class="tab-pane fade" id="history" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Book</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Issued</th>
                                    <th class="px-4 py-3">Returned</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($history->num_rows > 0): ?>
                                    <?php while($h = $history->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-4 fw-medium"><?php echo htmlspecialchars($h['title']); ?></td>
                                            <td class="px-4"><?php echo htmlspecialchars($h['full_name']); ?></td>
                                            <td class="px-4 text-muted small"><?php echo date('M d, Y', strtotime($h['request_date'])); ?></td>
                                            <td class="px-4 text-muted small"><?php echo $h['returned_at'] ? date('M d, Y H:i', strtotime($h['returned_at'])) : '-'; ?></td>
                                            <td class="px-4">
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?php echo ucfirst($h['status']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No history available yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Add Book to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="backend/actions/library_actions.php" method="POST">
                    <input type="hidden" name="action" value="add_book">
                    <div class="mb-3">
                        <label class="form-label">Book Title</label>
                        <input type="text" name="title" class="form-control bg-light border-0" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control bg-light border-0">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select bg-light border-0">
                                <option value="Fiction">Fiction</option>
                                <option value="Non-Fiction">Non-Fiction</option>
                                <option value="Science">Science</option>
                                <option value="History">History</option>
                                <option value="Academic">Academic</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control bg-light border-0" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3"></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lend Book Modal -->
<div class="modal fade" id="lendBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Lend Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="backend/actions/library_actions.php" method="POST">
                    <input type="hidden" name="action" value="lend_book">
                    <div class="mb-3">
                        <label class="form-label">Select Book</label>
                        <select name="book_id" id="lendBookSelect" class="form-select bg-light border-0" required>
                            <option value="">Choose a book...</option>
                            <?php 
                            // Reset pointer for dropdown
                            $inventory->data_seek(0);
                            while($b = $inventory->fetch_assoc()): 
                                if($b['available_copies'] > 0):
                            ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['title']); ?> (<?php echo $b['available_copies']; ?> avail)</option>
                            <?php 
                                endif;
                            endwhile; 
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Email</label>
                        <input type="email" name="user_email" class="form-control bg-light border-0" placeholder="borrower@example.com" required>
                        <div class="form-text">User must be registered on Boichokro.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Return Due Date</label>
                        <input type="date" name="return_date" class="form-control bg-light border-0" required value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">Confirm Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function fillLendModal(bookId, bookTitle) {
    const modal = new bootstrap.Modal(document.getElementById('lendBookModal'));
    const select = document.getElementById('lendBookSelect');
    select.value = bookId;
    modal.show();
}

function approveRequest(bookingId, bookTitle, userEmail) {
    document.getElementById('approveBookingId').value = bookingId;
    document.getElementById('approveBookTitle').textContent = bookTitle;
    document.getElementById('approveUserEmail').textContent = userEmail;
    new bootstrap.Modal(document.getElementById('approveRequestModal')).show();
}
</script>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Approve Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Approve loan for <strong id="approveBookTitle"></strong> to <strong id="approveUserEmail"></strong>?</p>
                <form action="backend/actions/library_actions.php" method="POST">
                    <input type="hidden" name="action" value="approve_request">
                    <input type="hidden" name="booking_id" id="approveBookingId">
                    
                    <div class="mb-4">
                        <label class="form-label">Return Due Date</label>
                        <input type="date" name="return_date" class="form-control bg-light border-0" required value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success rounded-pill py-2">Confirm & Issue Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'backend/includes/footer.php'; ?>

