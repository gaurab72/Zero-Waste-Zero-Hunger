<?php
// src/functions.php

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($path) {
    header("Location: $path");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}


function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Flash messages helpers (optional but good for UX)
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
// Notification System - Targeted to specific users
function addNotification($pdo, $type, $message, $user_id = null) {
    try {
        // If user_id is null, it's a system-wide broadcast or admin notification (role='admin')
        if ($user_id === null) {
            $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $user_id = $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $type, $message]);
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
    }
}

function getUnreadNotifications($pdo, $limit = 5) {
    if (!isset($_SESSION['user_id'])) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getUnreadCount($pdo) {
    if (!isset($_SESSION['user_id'])) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// CSRF Protection System
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        setFlash('error', 'Security session expired. Please try again.');
        return false;
    }
    return true;
}

function csrfInput() {
    $token = generateCSRFToken();
    return "<input type='hidden' name='csrf_token' value='$token'>";
}

function formatCurrency($amount) {
    return "Rs. " . number_format($amount, 2);
}


// System Usage Tracking (NGO Consumption)
function logFoodAction($pdo, $listing_id, $ngo_id, $action, $details = '') {
    try {
        $stmt = $pdo->prepare("INSERT INTO system_logs (action, details, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$action, "Listing #$listing_id: $details", $ngo_id]);
    } catch (Exception $e) {
        error_log("Logging Error: " . $e->getMessage());
    }
}
?>
