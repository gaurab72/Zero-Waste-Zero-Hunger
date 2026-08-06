<?php
// src/auth.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

session_start();

// REGISTRATION LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_btn'])) {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirect('../public/register.php');
    }

    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']); // donor, ngo, volunteer
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        setFlash('error', 'All fields are required');
        redirect('../public/register.php');
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->rowCount() > 0) {
        setFlash('error', 'Email or Username already taken');
        redirect('../public/register.php');
    }

    // Create User
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashed_password, $role]);
        
        // Notify Admin
        // Notify Admin
        addNotification($pdo, 'New User', "New $role registered: $username ($email)", null);

        
        setFlash('success', 'Registration successful! Please login.');
        redirect('../public/login.php');
    } catch (PDOException $e) {
        setFlash('error', 'Registration failed: ' . $e->getMessage());
        redirect('../public/register.php');
    }
}

// LOGIN LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        error_log("CSRF Failure: Token mismatch or missing for " . ($email ?? 'unknown'));
        redirect('../public/login.php');
    }

    $email = sanitize($_POST['email']);
    $password = $_POST['password'];


    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $login_type = $_POST['login_type'] ?? 'user';

    if ($user && password_verify($password, $user['password_hash'])) {
        error_log("Login SUCCESS for: " . $email);
        
        // STRICT LOGIN SEPARATION
        if ($login_type === 'admin') {
            // Case 1: Admin Portal Login
            if ($user['role'] !== 'admin') {
                setFlash('error', 'Access Denied: You are not an Admin.');
                redirect('../public/admin_login.php');
                exit; // Stop execution
            }
            // Admin Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            redirect('../public/admin/dashboard.php');
            
        } else {
            // Case 2: Normal User Login
            if ($user['role'] === 'admin') {
                setFlash('error', 'Admins must login via Admin Portal');
                redirect('../public/admin_login.php');
                exit;
            }
            
            // User Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            if ($user['role'] === 'donor') {
                redirect('../public/donate.php');
            }
            if ($user['role'] === 'volunteer') {
                redirect('../public/dashboard_volunteer.php');
            }
            redirect('../public/dashboard.php');
        }

    } else {
        error_log("Credential Failure for: " . $email . " - User found: " . ($user ? 'Yes' : 'No'));
        setFlash('error', 'Invalid credentials');
        /* Redirect back to source */
        if (isset($_POST['login_type']) && $_POST['login_type'] === 'admin') {
            redirect('../public/admin_login.php');
        } else {
            redirect('../public/login.php');
        }
    }
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('../public/index.php');
}
?>
