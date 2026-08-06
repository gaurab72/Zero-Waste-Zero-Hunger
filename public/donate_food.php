<?php
require_once '../config/db.php';
require_once '../src/functions.php';
session_start();

// Access Control
if (!isLoggedIn() || $_SESSION['role'] !== 'donor') {
    setFlash('error', 'Access denied. Please login as a donor.');
    redirect('login.php');
}

$flash = getFlash();

// Handle Food Donation Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate_food_btn'])) {
    $donor_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $food_type = sanitize($_POST['food_type']);
    $quantity = sanitize($_POST['quantity']);
    $expiry = $_POST['expiry_datetime'];
    $location = sanitize($_POST['pickup_location']); // General area e.g. "Koteshwor"
    $address = sanitize($_POST['pickup_address']);   // Specific address
    
    // Image Upload
    $image_path = null;
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === 0) {
        $upload_dir = 'assets/uploads/food/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid('food_', true) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['food_image']['tmp_name'], $upload_dir . $new_filename)) {
                $image_path = $upload_dir . $new_filename;
            }
        }
    }

    // Insert into Database
    try {
        $stmt = $pdo->prepare("INSERT INTO food_listings (donor_id, title, description, food_type, quantity, expiry_datetime, pickup_location, pickup_address, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$donor_id, $title, $description, $food_type, $quantity, $expiry, $location, $address, $image_path]);
        
        setFlash('success', 'Food donation listed successfully! NGOs will be notified.');
        redirect('dashboard.php'); // Or stay here/redirect to list
    } catch (PDOException $e) {
        setFlash('error', 'Failed to list donation: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Food | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 60px 0;">
        <div style="max-width: 800px; margin: 0 auto;">
            <a href="donate.php" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
                &larr; Back to Options
            </a>
            
            <div class="glass-card">
                <h2 class="text-gradient" style="margin-bottom: 20px;">Donate Food</h2>
                <p style="color: var(--text-muted); margin-bottom: 30px;">
                    Fill in the details of the food you wish to donate. Please ensure the food is safe to consume.
                </p>

                <?php if($flash): ?>
                    <div style="padding: 15px; border-radius: 8px; margin-bottom: 25px; 
                        background: <?php echo $flash['type'] == 'error' ? 'rgba(255,0,80,0.1)' : 'rgba(0,255,136,0.1)'; ?>; 
                        border: 1px solid <?php echo $flash['type'] == 'error' ? '#ff0055' : 'var(--primary)'; ?>; 
                        color: var(--text-main);">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <!-- Left Column -->
                        <div>
                            <div class="form-group">
                                <label class="form-label">Food Title</label>
                                <input type="text" name="title" class="form-input" placeholder="e.g. 20 Packets of Rice" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Food Type</label>
                                <select name="food_type" class="form-input" style="appearance: auto;">
                                    <option value="veg">Vegetarian</option>
                                    <option value="non-veg">Non-Vegetarian</option>
                                    <option value="vegan">Vegan</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <input type="text" name="quantity" class="form-input" placeholder="e.g. 5kg, 10 plates" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Expiry Date & Time</label>
                                <input type="datetime-local" name="expiry_datetime" class="form-input" required>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <div class="form-group">
                                <label class="form-label">Pickup Area (City/Location)</label>
                                <input type="text" name="pickup_location" class="form-input" placeholder="e.g. Kathmandu, Thamel" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Detailed Pickup Address</label>
                                <textarea name="pickup_address" class="form-input" rows="3" placeholder="Street name, house number, landmark..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Food Image (Optional)</label>
                                <input type="file" name="food_image" class="form-input" accept="image/*" style="padding-top: 12px;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Any specific details about the food, storage instructions, etc."></textarea>
                    </div>

                    <button type="submit" name="donate_food_btn" class="btn btn-primary" style="width: 100%; margin-top: 20px; padding: 15px;">
                        List Food Donation
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>
