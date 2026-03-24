<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Initialize auth
$auth = new Auth($pdo);

// Logout
$auth->logout();

// Redirect to login
header('Location: login.php');
exit();
?>