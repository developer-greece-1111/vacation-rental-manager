<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Initialize auth and require login
$auth = new Auth($pdo);
$auth->requireLogin();

// Get current user
$user = $auth->getCurrentUser();

// Get some stats
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch();

// Get recent activity (just a placeholder for now)
$recent_users = [];
$stmt = $pdo->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <h4>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>! 👋</h4>
            <p>Your vacation rental management system is ready.</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="card-text"><?php echo $total_users['count']; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Properties</h5>
                <h2 class="card-text">0</h2>
                <small>Coming soon</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Bookings</h5>
                <h2 class="card-text">0</h2>
                <small>Coming soon</small>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>Recent Registrations</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_users)): ?>
                        <tr>
                            <td colspan="3" class="text-center">No users yet</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <strong>🚀 Next Steps:</strong>
            <ul class="mt-2 mb-0">
                <li>Properties module - Add your rental properties</li>
                <li>Guests module - Manage guest information</li>
                <li>Bookings calendar - Track reservations</li>
                <li>AI insights - Get smart recommendations</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>