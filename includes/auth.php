<?php
/**
 * Authentication System
 * Handles user registration, login, and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Register a new user
     */
    public function register($username, $email, $password, $full_name = '') {
        // Validate inputs
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if username or email already exists
        $stmt = $this->pdo->prepare("
            SELECT id FROM users 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'errors' => ['Username or email already exists']];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, full_name)
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
            return ['success' => true, 'message' => 'Registration successful! Please login.'];
        }
        
        return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
    }
    
    /**
     * Login user
     */
    public function login($username_or_email, $password) {
        // Validate inputs
        if (empty($username_or_email) || empty($password)) {
            return ['success' => false, 'error' => 'Please enter username/email and password'];
        }
        
        // Find user by username or email
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password, full_name, is_active
            FROM users
            WHERE username = ? OR email = ?
            LIMIT 1
        ");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'error' => 'Account is deactivated. Contact support.'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Update last login
        $stmt = $this->pdo->prepare("
            UPDATE users SET last_login = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
        return ['success' => true, 'message' => 'Login successful!'];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn() && isset($_SESSION['user_id'])) {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, full_name
                FROM users
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
        return null;
    }
    
    /**
     * Require login - redirect if not authenticated
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Redirect if already logged in
     */
    public function requireGuest() {
        if ($this->isLoggedIn()) {
            header('Location: dashboard.php');
            exit();
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $_SESSION = [];
        session_destroy();
        return true;
    }
}
?>