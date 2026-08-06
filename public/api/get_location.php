<?php
// public/api/get_location.php
require_once '../../config/db.php';
require_once '../../src/functions.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$target_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($target_user_id) {
    $stmt = $pdo->prepare("SELECT latitude, longitude, updated_at FROM user_locations WHERE user_id = ?");
    $stmt->execute([$target_user_id]);
    $location = $stmt->fetch();

    if ($location) {
        // Only return if updated within the last 2 minutes
        $updated_at = strtotime($location['updated_at']);
        $now = time();
        if (($now - $updated_at) > 120) {
            echo json_encode(['error' => 'Location is stale/offline']);
        } else {
            echo json_encode($location);
        }
    } else {
        echo json_encode(['error' => 'Location not found']);
    }
} else {
    echo json_encode(['error' => 'No user ID provided']);
}
