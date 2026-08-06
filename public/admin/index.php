<?php
// public/admin/index.php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
} else {
    // If not logged in or not admin, redirect to Admin Login
    header('Location: ../admin_login.php');
    exit;
}
?>
