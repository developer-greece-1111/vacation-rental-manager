<?php
/**
 * Database Connection Configuration
 * Vacation Rental Management System
 */

// Database credentials
$host = 'db.wwgvjpgkxakzaglabzwc.supabase.co';
$port = '5432';
$dbname = 'postgres';
$user = 'postgres';
$password = '14051975AAss@@@!';

try {
    // Create PDO connection with SSL
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Set timezone to UTC
    $pdo->exec("SET TIME ZONE 'UTC'");
    
} catch (PDOException $e) {
    // Log error for debugging
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Show user-friendly message
    die("System temporarily unavailable. Please try again later.");
}

// Return the connection
return $pdo;
?>