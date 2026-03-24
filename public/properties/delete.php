<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

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

// Verify property belongs to user
$stmt = $pdo->prepare("
    SELECT id FROM properties 
    WHERE id = :id AND user_id = :user_id
");
$stmt->execute([':id' => $property_id, ':user_id' => $user['id']]);

if (!$stmt->fetch()) {
    header('Location: index.php');
    exit();
}

// Delete property
try {
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $property_id, ':user_id' => $user['id']]);
    
    // Redirect with success message
    header('Location: index.php?deleted=1');
    exit();
    
} catch (PDOException $e) {
    // If there are related bookings, show error
    header('Location: index.php?error=cannot_delete');
    exit();
}
?>