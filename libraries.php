<?php 
require_once 'backend/includes/db.php';
require_once 'backend/includes/header.php'; 

$location = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

$sql = "SELECT * FROM libraries WHERE status = 'approved'";

if (!empty($location)) {
    $sql .= " AND (city LIKE '%$location%' OR area LIKE '%$location%' OR address LIKE '%$location%' OR library_name LIKE '%$location%')";
}

$result = $conn->query($sql);
?>

<div class="bg-light py-5">
    <div class="container">
         <div class="text-center mb-5">
            <h1 class="fw-bold text-dark display-5 mb-2">Local Libraries</h1>
            <p class="text-muted mb-0">Connect with lending hubs in your area.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 max-width-600 mx-auto">
            <form action="libraries.php" method="GET" class="row g-3">
                <div class="col-md-9">
                    <div class="input-group bg-white border rounded-pill px-2">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="location" class="form-control border-0 bg-transparent" placeholder="Find libraries by location or name..." value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden rounded-3">
                            <img src="<?php echo htmlspecialchars($row['image_url'] ?: 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&q=80&w=800'); ?>" class="card-img-top object-fit-cover" alt="Library" style="height: 250px;">
                            <div class="card-body p-4">
                                <h4 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($row['library_name']); ?></h4>
                                <div class="badge bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-pill px-3 mb-3">
                                    <?php echo ucfirst(htmlspecialchars($row['library_type'])); ?>
                                </div>
                                <div class="d-flex align-items-center text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($row['address']); ?></span>
                                </div>
                                <div class="d-flex align-items-center text-muted mb-3">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($row['area']) . ', ' . htmlspecialchars($row['city']); ?></span>
                                </div>
                                <hr class="border-secondary-subtle">
                                <div class="d-flex justify-content-end align-items-center">
                                    <a href="library_profile.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-outline-primary rounded-pill btn-sm">Visit Profile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                 <div class="col-12 text-center py-5">
                    <h3 class="mt-3 text-muted">No libraries found.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'backend/includes/footer.php'; ?>
