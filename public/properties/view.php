<?php
$pageTitle = "Property Details";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

// Initialize auth and require login
$auth = new Auth($pdo);
$auth->requireLogin();

$user = $auth->getCurrentUser();

// Get property ID from URL
$property_id = $_GET['id'] ?? '';

if (empty($property_id)) {
    header('Location: index.php');
    exit();
}

// Get property details
$stmt = $pdo->prepare("
    SELECT * FROM properties 
    WHERE id = :id AND user_id = :user_id
");
$stmt->execute([':id' => $property_id, ':user_id' => $user['id']]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: index.php');
    exit();
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?php echo htmlspecialchars($property['name']); ?></h4>
                <div>
                    <a href="edit.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="index.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge <?php echo $property['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $property['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Price per Night</h6>
                        <h3 class="text-primary">€<?php echo number_format($property['price_per_night'], 2); ?></h3>
                    </div>
                </div>
                
                <?php if ($property['description']): ?>
                    <div class="mb-4">
                        <h6 class="text-muted">Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Location</h6>
                        <?php if ($property['address']): ?>
                            <p><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['address']); ?></p>
                        <?php endif; ?>
                        <?php if ($property['city']): ?>
                            <p><i class="bi bi-building"></i> <?php echo htmlspecialchars($property['city']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Details</h6>
                        <p>
                            <i class="bi bi-door-open"></i> <?php echo $property['bedrooms']; ?> Bedrooms<br>
                            <i class="bi bi-droplet"></i> <?php echo $property['bathrooms']; ?> Bathrooms<br>
                            <i class="bi bi-person"></i> Max <?php echo $property['max_guests']; ?> Guests
                        </p>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="bi bi-calendar"></i> Next Steps:</strong>
                    <p class="mb-0 mt-2">
                        You can now create bookings for this property from the 
                        <a href="../bookings/create.php?property_id=<?php echo $property['id']; ?>">Bookings</a> section.
                    </p>
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    Created: <?php echo date('F j, Y', strtotime($property['created_at'])); ?>
                    <?php if ($property['updated_at'] != $property['created_at']): ?>
                        | Last updated: <?php echo date('F j, Y', strtotime($property['updated_at'])); ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>