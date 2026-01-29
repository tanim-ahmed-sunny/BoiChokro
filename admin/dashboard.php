<?php 
require_once '../backend/includes/db.php';

session_start();
// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user role
$user_id = $_SESSION['user_id'];
$userQ = $conn->query("SELECT role FROM users WHERE id=$user_id");
$user = $userQ->fetch_assoc();

if ($user['role'] !== 'admin') {
    die("Access Denied. Admins only.");
}

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

$tab = $_GET['tab'] ?? 'dashboard';

// Global Stats (Needed for sidebar or quick headers if any, or just for dashboard)
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalBooks = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$pendingBooksCount = $conn->query("SELECT COUNT(*) as c FROM books WHERE status='pending'")->fetch_assoc()['c'];
$totalLibs = $conn->query("SELECT COUNT(*) as c FROM libraries")->fetch_assoc()['c'];

// Data Fetching based on tab
$displayData = null;
switch($tab) {
    case 'users':
        $displayData = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
        break;
    case 'books':
        $displayData = $conn->query("SELECT b.*, u.username FROM books b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
        break;
    case 'libraries':
        $displayData = $conn->query("SELECT l.*, u.username as owner FROM libraries l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC");
        break;
    case 'reports':
        $displayData = $conn->query("SELECT r.*, u.username as reporter FROM reports r JOIN users u ON r.reporter_id = u.id ORDER BY r.created_at DESC");
        break;
    default:
        $pendingList = $conn->query("SELECT b.*, u.username FROM books b JOIN users u ON b.user_id = u.id WHERE b.status='pending' ORDER BY b.created_at ASC");
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Boichokro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark sticky-top">
  <div class="container-fluid">
    <span class="navbar-brand d-flex align-items-center gap-2 mb-0 h1">
        <i class="bi bi-speedometer2 text-primary"></i> Boichokro Admin
    </span>
    <div class="d-flex gap-3">
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createLibraryModal">
            <i class="bi bi-plus-lg me-1"></i>Create Library
        </button>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
    </div>
  </div>
</nav>

<div class="container-fluid py-4">
    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-md-2 d-none d-md-block">
            <div class="list-group shadow-sm">
                <a href="?tab=dashboard" class="list-group-item list-group-item-action <?php echo $tab == 'dashboard' ? 'active' : ''; ?>"><i class="bi bi-grid-fill me-2"></i>Dashboard</a>
                <a href="?tab=users" class="list-group-item list-group-item-action <?php echo $tab == 'users' ? 'active' : ''; ?>"><i class="bi bi-people me-2"></i>Users</a>
                <a href="?tab=books" class="list-group-item list-group-item-action <?php echo $tab == 'books' ? 'active' : ''; ?>"><i class="bi bi-book me-2"></i>All Books</a>
                <a href="?tab=libraries" class="list-group-item list-group-item-action <?php echo $tab == 'libraries' ? 'active' : ''; ?>"><i class="bi bi-building me-2"></i>Libraries</a>
                <a href="?tab=reports" class="list-group-item list-group-item-action <?php echo $tab == 'reports' ? 'active' : ''; ?>"><i class="bi bi-flag me-2"></i>Reports</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <!-- Tab Content -->
            <?php if ($tab == 'dashboard'): ?>
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="display-6 fw-bold"><?php echo $totalUsers; ?></div>
                                <div class="small opacity-75">Total Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-success text-white h-100">
                            <div class="card-body">
                                <div class="display-6 fw-bold"><?php echo $totalBooks; ?></div>
                                <div class="small opacity-75">Total Books</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-warning h-100">
                            <div class="card-body">
                                <div class="display-6 fw-bold"><?php echo $pendingBooksCount; ?></div>
                                <div class="small opacity-75">Pending Reviews</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-info text-white h-100">
                            <div class="card-body">
                                <div class="display-6 fw-bold"><?php echo $totalLibs; ?></div>
                                <div class="small opacity-75">Libraries</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Pending Book Approvals</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Book Title</th>
                                        <th>Author</th>
                                        <th>Posted By</th>
                                        <th>Price</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pendingList->num_rows > 0): ?>
                                        <?php while($book = $pendingList->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4 fw-medium">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="<?php echo htmlspecialchars($book['images']); ?>" class="rounded" width="40" height="40" style="object-fit: cover;">
                                                        <?php echo htmlspecialchars($book['title']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                                <td>@<?php echo htmlspecialchars($book['username']); ?></td>
                                                <td>$<?php echo $book['price']; ?></td>
                                                <td class="small text-muted"><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" action="../backend/actions/admin_approve.php" class="d-flex gap-2">
                                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No pending books to review.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab == 'users'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">User Management</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($u = $displayData->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo ucfirst($u['role']); ?></span></td>
                                            <td>
                                                <span class="badge <?php echo $u['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($u['status']); ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                            <td>
                                                <form action="../backend/actions/admin_actions.php" method="POST">
                                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                    <input type="hidden" name="tab" value="users">
                                                    <?php if($u['status'] == 'active'): ?>
                                                        <button type="submit" name="action" value="revoke_user" class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspend this user?')">Revoke Access</button>
                                                    <?php else: ?>
                                                        <button type="submit" name="action" value="activate_user" class="btn btn-sm btn-outline-success">Activate</button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab == 'books'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">All Books</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Book</th>
                                        <th>Poster</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($b = $displayData->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?php echo htmlspecialchars($b['images']); ?>" class="rounded" width="30" height="30" style="object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($b['title']); ?></div>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($b['author']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>@<?php echo htmlspecialchars($b['username']); ?></td>
                                            <td><?php echo htmlspecialchars($b['category']); ?></td>
                                            <td><span class="badge bg-<?php echo $b['status'] == 'approved' ? 'success' : ($b['status'] == 'pending' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($b['status']); ?></span></td>
                                            <td>৳<?php echo $b['price']; ?></td>
                                            <td>
                                                <form action="../backend/actions/admin_actions.php" method="POST">
                                                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                                    <input type="hidden" name="tab" value="books">
                                                    <button type="submit" name="action" value="revoke_book" class="btn btn-sm btn-outline-danger" onclick="return confirm('Revoke this book listing?')">Revoke</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab == 'libraries'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Libraries</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Library Name</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Owner</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($l = $displayData->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($l['library_name']); ?></td>
                                            <td><?php echo ucfirst($l['library_type']); ?></td>
                                            <td><?php echo htmlspecialchars($l['area'] . ', ' . $l['city']); ?></td>
                                            <td>@<?php echo htmlspecialchars($l['owner']); ?></td>
                                            <td><span class="badge bg-<?php echo $l['status'] == 'approved' ? 'success' : 'danger'; ?>"><?php echo ucfirst($l['status']); ?></span></td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick='editLibrary(<?php echo htmlspecialchars(json_encode($l), ENT_QUOTES, "UTF-8"); ?>)'>
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <form action="../backend/actions/admin_actions.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?php echo $l['id']; ?>">
                                                        <input type="hidden" name="tab" value="libraries">
                                                        <button type="submit" name="action" value="revoke_library" class="btn btn-sm btn-outline-danger" onclick="return confirm('Revoke library access?')">Revoke</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab == 'reports'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">User Reports</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Reporter</th>
                                        <th>Reported Type</th>
                                        <th>ID</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($displayData->num_rows > 0): ?>
                                        <?php while($r = $displayData->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4">@<?php echo htmlspecialchars($r['reporter']); ?></td>
                                                <td><span class="badge bg-info text-dark"><?php echo ucfirst($r['reported_type']); ?></span></td>
                                                <td>#<?php echo $r['reported_id']; ?></td>
                                                <td class="small"><?php echo htmlspecialchars($r['reason']); ?></td>
                                                <td><span class="badge bg-<?php echo $r['status'] == 'pending' ? 'warning' : 'success'; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                                                <td>
                                                    <?php if($r['status'] == 'pending'): ?>
                                                        <form action="../backend/actions/admin_actions.php" method="POST" class="d-flex gap-1">
                                                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                                            <input type="hidden" name="tab" value="reports">
                                                            <input type="hidden" name="action" value="manage_report">
                                                            <button type="submit" name="status" value="resolved" class="btn btn-sm btn-success">Resolve</button>
                                                            <button type="submit" name="status" value="dismissed" class="btn btn-sm btn-outline-secondary">Dismiss</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Handled</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No reports found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>

<!-- Create Library Modal -->
<div class="modal fade" id="createLibraryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Create Library Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../backend/actions/create_library.php" method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-12"><h6 class="text-primary border-bottom pb-2">Account Details</h6></div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="e.g. dulibrary">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="lib@example.com">
                </div>
                 <div class="col-md-12">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Initial password">
                </div>
                
                <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">Library Details</h6></div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Library Name</label>
                    <input type="text" name="library_name" class="form-control" required placeholder="e.g. Dhaka University Library">
                </div>
                <div class="col-md-4">
                     <label class="form-label small fw-bold">Type</label>
                     <select name="library_type" class="form-select">
                         <option value="university">University</option>
                         <option value="public">Public</option>
                     </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                 <div class="col-md-6">
                    <label class="form-label small fw-bold">City</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Area</label>
                    <input type="text" name="area" class="form-control" required>
                </div>
                  <div class="col-md-12">
                    <label class="form-label small fw-bold">Full Address</label>
                    <textarea name="address" class="form-control" rows="2" required></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Library Image</label>
                    <input type="file" name="library_image" class="form-control" accept="image/*">
                    <div class="form-text">Optional: Bright, clear exterior or interior photo.</div>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Create Library</button>
          </div>
      </form>
    </div>
  </div>
</div>
<!-- Edit Library Modal -->
<div class="modal fade" id="editLibraryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Edit Library Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="../backend/actions/admin_update_library.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="library_id" id="edit_lib_id">
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Library Name</label>
                    <input type="text" name="library_name" id="edit_lib_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                     <label class="form-label small fw-bold">Type</label>
                     <select name="library_type" id="edit_lib_type" class="form-select">
                         <option value="university">University</option>
                         <option value="public">Public</option>
                     </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Phone</label>
                    <input type="text" name="phone" id="edit_lib_phone" class="form-control">
                </div>
                 <div class="col-md-6">
                    <label class="form-label small fw-bold">City</label>
                    <input type="text" name="city" id="edit_lib_city" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Area</label>
                    <input type="text" name="area" id="edit_lib_area" class="form-control" required>
                </div>
                 <div class="col-md-12">
                    <label class="form-label small fw-bold">Full Address</label>
                    <textarea name="address" id="edit_lib_address" class="form-control" rows="2" required></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-bold">Change Library Image</label>
                    <div class="mb-2">
                        <img id="edit_lib_img_preview" src="" class="rounded" width="100" height="60" style="object-fit: cover; display: none;">
                    </div>
                    <input type="file" name="library_image" class="form-control" accept="image/*">
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editLibrary(lib) {
    document.getElementById('edit_lib_id').value = lib.id;
    document.getElementById('edit_lib_name').value = lib.library_name;
    document.getElementById('edit_lib_type').value = lib.library_type;
    document.getElementById('edit_lib_phone').value = lib.contact_phone;
    document.getElementById('edit_lib_city').value = lib.city;
    document.getElementById('edit_lib_area').value = lib.area;
    document.getElementById('edit_lib_address').value = lib.address;
    
    if (lib.image_url) {
        document.getElementById('edit_lib_img_preview').src = '../' + lib.image_url;
        document.getElementById('edit_lib_img_preview').style.display = 'block';
    } else {
        document.getElementById('edit_lib_img_preview').style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('editLibraryModal')).show();
}
</script>
</body>
</html>
