<?php
/**
 * Live Feed API — returns latest available food listings as JSON
 * Called by the home page every 30 seconds via AJAX
 */
require_once '../../config/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT f.id, f.title, f.food_type, f.quantity, f.pickup_location,
               DATE_FORMAT(f.expiry_datetime, '%h:%i %p') as expiry_time,
               f.created_at,
               u.username as donor_name
        FROM food_listings f
        JOIN users u ON f.donor_id = u.id
        WHERE f.status = 'available' AND f.expiry_datetime > NOW()
        ORDER BY f.created_at DESC
        LIMIT 3
    ");
    $feed = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_money = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM money_donations")->fetchColumn();

    echo json_encode([
        'success'     => true,
        'feed'        => $feed,
        'total_money' => number_format((float)$total_money),
        'timestamp'   => date('H:i:s')
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
