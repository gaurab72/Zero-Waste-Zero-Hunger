<?php
// public/api/update_location.php
require_once '../../config/db.php';
require_once '../../src/functions.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action']) && $data['action'] === 'clear') {
    $stmt = $pdo->prepare("DELETE FROM user_locations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'message' => 'Location cleared']);
} elseif (isset($data['latitude']) && isset($data['longitude'])) {
    $lat = (float)$data['latitude'];
    $lng = (float)$data['longitude'];

    $stmt = $pdo->prepare("INSERT INTO user_locations (user_id, latitude, longitude) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE latitude = ?, longitude = ?, updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([$user_id, $lat, $lng, $lat, $lng]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Invalid data']);
}
