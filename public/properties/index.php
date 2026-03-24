<?php
$pageTitle = "Properties";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

// Initialize auth and require login
$auth = new Auth($pdo);
$auth->requireLogin();

$user = $auth->getCurrentUser();

// Handle search/filter
$search = $_GET['search'] ?? '';
$city = $_GET['city'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build query with filters
$sql = "SELECT * FROM properties WHERE user_id = :user_id";
$params = [':user_id' => $user['id']];

if (!empty($search)) {
    $sql .= " AND (name ILIKE :search OR description ILIKE :search OR city ILIKE :search)";
    $params[':search'] = "%{$search}%";
}

if (!empty($city)) {
    $sql .= " AND city ILIKE :city";
    $params[':city'] = "%{$city}%";
}

if (!empty($min_price)) {
    $sql .= " AND price_per_night >= :min_price";
    $params[':min_price'] = $min_price;
}

if (!empty($max_price)) {
    $sql .= " AND price_per_night <= :max_price";
    $params[':max_price'] = $max_price;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Get unique cities for filter dropdown
$cityStmt = $pdo->prepare("SELECT DISTINCT city FROM properties WHERE user_id = :user_id AND city IS NOT NULL ORDER BY city");
$cityStmt->execute([':user_id' => $user['id']]);
$cities = $cityStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Properties</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Property
    </a>
</div>

<!-- Search and Filter Bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Name, description, or city...">
            </div>
            <div class="col-md-2">
                <label class="form-label">City</label>
                <select class="form-select" name="city">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['city']); ?>" 
                                <?php echo $city == $c['city'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['city']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Min Price (€)</label>
                <input type="number" class="form-control" name="min_price" 
                       value="<?php echo htmlspecialchars($min_price); ?>"
                       placeholder="Min">
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Price (€)</label>
                <input type="number" class="form-control" name="max_price" 
                       value="<?php echo htmlspecialchars($max_price); ?>"
                       placeholder="Max">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Properties Grid -->
<?php if (empty($properties)): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i> No properties found.
        <?php if (!empty($search) || !empty($city) || !empty($min_price) || !empty($max_price)): ?>
            <br><a href="index.php">Clear filters</a>
        <?php else: ?>
            <br><a href="create.php" class="btn btn-primary mt-2">Add Your First Property</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($properties as $property): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['name']); ?></h5>
                            <span class="badge <?php echo $property['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $property['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        
                        <?php if ($property['city']): ?>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['city']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="card-text">
                            <?php 
                            $desc = htmlspecialchars($property['description'] ?? '');
                            echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                            ?>
                        </p>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="bi bi-door-open"></i> <?php echo $property['bedrooms']; ?> beds
                                </small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?php echo $property['max_guests']; ?> guests
                                </small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-12">
                                <h4 class="text-primary mb-0">€<?php echo number_format($property['price_per_night'], 2); ?></h4>
                                <small class="text-muted">per night</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="btn-group w-100">
                            <a href="view.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="edit.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete.php?id=<?php echo $property['id']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this property? This will remove all associated bookings.')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>