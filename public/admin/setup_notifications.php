<?php
// public/admin/setup_notifications.php
require_once '../../config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Table 'notifications' created successfully or already exists.";
    
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
