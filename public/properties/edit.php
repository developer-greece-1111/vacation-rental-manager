<?php
$pageTitle = "Edit Property";
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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $bedrooms = (int)($_POST['bedrooms'] ?? 1);
    $bathrooms = (int)($_POST['bathrooms'] ?? 1);
    $max_guests = (int)($_POST['max_guests'] ?? 2);
    $price_per_night = (float)($_POST['price_per_night'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Property name is required";
    }
    
    if ($price_per_night <= 0) {
        $errors[] = "Price per night must be greater than 0";
    }
    
    if ($max_guests < 1) {
        $errors[] = "Max guests must be at least 1";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE properties 
                SET name = :name,
                    description = :description,
                    address = :address,
                    city = :city,
                    bedrooms = :bedrooms,
                    bathrooms = :bathrooms,
                    max_guests = :max_guests,
                    price_per_night = :price_per_night,
                    is_active = :is_active,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND user_id = :user_id
            ");
            
            $stmt->execute([
                ':id' => $property_id,
                ':user_id' => $user['id'],
                ':name' => $name,
                ':description' => $description,
                ':address' => $address,
                ':city' => $city,
                ':bedrooms' => $bedrooms,
                ':bathrooms' => $bathrooms,
                ':max_guests' => $max_guests,
                ':price_per_night' => $price_per_night,
                ':is_active' => $is_active
            ]);
            
            $success = "Property updated successfully!";
            
            // Refresh property data
            $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':id' => $property_id, ':user_id' => $user['id']]);
            $property = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4>Edit Property</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Property Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($property['name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($property['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($property['address'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($property['city'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="bedrooms" class="form-label">Bedrooms</label>
                            <input type="number" class="form-control" id="bedrooms" name="bedrooms" 
                                   value="<?php echo $property['bedrooms']; ?>" min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="bathrooms" class="form-label">Bathrooms</label>
                            <input type="number" class="form-control" id="bathrooms" name="bathrooms" 
                                   value="<?php echo $property['bathrooms']; ?>" min="0" step="0.5">
                        </div>
                        <div class="col-md-4">
                            <label for="max_guests" class="form-label">Max Guests *</label>
                            <input type="number" class="form-control" id="max_guests" name="max_guests" 
                                   value="<?php echo $property['max_guests']; ?>" min="1" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price_per_night" class="form-label">Price per Night (€) *</label>
                            <input type="number" class="form-control" id="price_per_night" name="price_per_night" 
                                   value="<?php echo $property['price_per_night']; ?>" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo $property['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Property is active and available for bookings
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="view.php?id=<?php echo $property['id']; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>