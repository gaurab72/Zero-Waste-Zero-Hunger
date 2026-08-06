<?php
session_start();

// Dependencies are now handled by navbar.php
// But we include them once here as well for logic that runs before navbar
require_once '../config/db.php';
require_once '../src/functions.php';


// Auth Check
if (!isLoggedIn()) redirect('login.php');

$user = getCurrentUser($pdo);
$role = $user['role'];
$flash = getFlash();

// --- ACTION SCRIPTING ---

// 1. Handle Post Food
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['post_food_btn']) || isset($_POST['post_food']))) {
    $donor_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $food_type = sanitize($_POST['food_type']);
    $quantity = sanitize($_POST['quantity']);
    $expiry = $_POST['expiry'];
    $location = sanitize($_POST['location']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO food_listings (donor_id, title, description, food_type, quantity, expiry_datetime, pickup_location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'available')");
        $stmt->execute([$donor_id, $title, $description, $food_type, $quantity, $expiry, $location]);
        
        // Notify NGOs about new food
        $ngo_stmt = $pdo->query("SELECT id FROM users WHERE role = 'ngo'");
        while($ngo = $ngo_stmt->fetch()) {
            addNotification($pdo, 'Food Listing', "New $food_type food available: $title in $location", $ngo['id']);
        }
        setFlash('success', 'Food listing posted successfully! NGOs notified.');
    } catch (PDOException $e) {
        setFlash('error', 'Database error: ' . $e->getMessage());
    }
    redirect('dashboard.php');
}

// Handle KYC Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_kyc'])) {
    if (isset($_FILES['kyc_document']) && $_FILES['kyc_document']['error'] == 0) {
        $upload_dir = 'uploads/kyc/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES['kyc_document']['name'], PATHINFO_EXTENSION));
        $new_name = "kyc_" . $user['id'] . "_" . time() . "." . $file_ext;
        $dest = $upload_dir . $new_name;
        
        if (move_uploaded_file($_FILES['kyc_document']['tmp_name'], $dest)) {
            $stmt = $pdo->prepare("UPDATE users SET kyc_file = ?, kyc_status = 'submitted' WHERE id = ?");
            $stmt->execute([$dest, $user['id']]);
            setFlash('success', 'Documents uploaded successfully! Waiting for approval.');
            redirect('dashboard.php');
        } else {
            setFlash('error', 'Failed to upload file.');
        }
    } else {
        setFlash('error', 'Please select a valid file.');
    }
}

// 2. NGO: Claim Food
if (isset($_GET['claim_id']) && $role === 'ngo') {
    $listing_id = $_GET['claim_id'];
    
    // Check if already claimed
    $check = $pdo->prepare("SELECT id FROM claims WHERE listing_id = ? AND ngo_id = ?");
    $check->execute([$listing_id, $user['id']]);
    
    if($check->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO claims (listing_id, ngo_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$listing_id, $user['id']]);
        
        // Update listing status
        $pdo->prepare("UPDATE food_listings SET status = 'claimed' WHERE id = ?")->execute([$listing_id]);
        
        // Log action
        logFoodAction($pdo, $listing_id, $user['id'], 'Claim', 'NGO interested in picking up');
        
        setFlash('success', 'Claim request sent!');
    }
    redirect('dashboard.php');
}

// --- DATA FETCHING ---
if ($role === 'donor') {
    $stmt = $pdo->prepare("SELECT * FROM food_listings WHERE donor_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $my_listings = $stmt->fetchAll();

    // Also fetch money donations
    $stmt = $pdo->prepare("SELECT * FROM money_donations WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $my_money_donations = $stmt->fetchAll();
} else {
    // NGO sees all available food
    $stmt = $pdo->query("SELECT f.*, u.username as donor_name, u.contact_number FROM food_listings f JOIN users u ON f.donor_id = u.id WHERE f.status = 'available' ORDER BY f.expiry_datetime ASC");
    $available_food = $stmt->fetchAll();

    // 2. My Logistics (Active Claims)
    $stmt = $pdo->prepare("
        SELECT c.id as claim_id, c.status as claim_status, c.volunteer_id as v_id, f.*, u_donor.username as donor_name, u_donor.contact_number as donor_phone, u_vol.username as volunteer_name, u_vol.contact_number as volunteer_phone
        FROM claims c 
        JOIN food_listings f ON c.listing_id = f.id 
        JOIN users u_donor ON f.donor_id = u_donor.id 
        LEFT JOIN users u_vol ON c.volunteer_id = u_vol.id
        WHERE c.ngo_id = ? AND c.status != 'completed'
    ");
    $stmt->execute([$user['id']]);
    $my_logistics = $stmt->fetchAll();

    // 3. Impact Stats (Total Collected)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE ngo_id = ? AND status = 'completed'");
    $stmt->execute([$user['id']]);
    $total_rescued = $stmt->fetchColumn();

    // 4. Financial Stats
    // Received Targeted Donations
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM money_donations WHERE receiver_id = ?");
    $stmt->execute([$user['id']]);
    $total_received = $stmt->fetchColumn() ?: 0;

    // Platform Wide (For Context / Transparency)
    $stmt = $pdo->query("SELECT SUM(amount) FROM money_donations");
    $total_money_raised = $stmt->fetchColumn() ?: 0;

    // Recent Targeted Donations
    $stmt = $pdo->prepare("SELECT * FROM money_donations WHERE receiver_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user['id']]);
    $my_received_donations = $stmt->fetchAll();
    
    // Recent General/Platform Donations (For Context)
    $stmt = $pdo->query("SELECT * FROM money_donations WHERE receiver_id IS NULL ORDER BY created_at DESC LIMIT 5");
    $recent_donations = $stmt->fetchAll();
}

// 3. NGO: Mark Verified/Collected
if (isset($_POST['mark_collected']) && $role === 'ngo') {
    $claim_id = $_POST['claim_id'];
    // Verify ownership and update claim status
    $stmt = $pdo->prepare("UPDATE claims SET status = 'completed' WHERE id = ? AND ngo_id = ?");
    $stmt->execute([$claim_id, $user['id']]);
    
    // Update listing status to collected
    $stmt = $pdo->prepare("UPDATE food_listings SET status = 'collected' WHERE id = (SELECT listing_id FROM claims WHERE id = ?)");
    $stmt->execute([$claim_id]);

    // Log action
    logFoodAction($pdo, $claim_id, $user['id'], 'Collected', 'Food successfully rescued and consumed');

    setFlash('success', 'Pickup confirmed! Impact recorded.');
    redirect('dashboard.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ZeroWaste</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        @media(max-width: 768px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
        .listing-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            background: rgba(255,255,255,0.1);
        }
        .status-available { color: var(--primary); background: rgba(0,255,136,0.1); }
        .status-claimed { color: var(--accent); background: rgba(255,0,85,0.1); }
        .badge-urgent { 
            background: #ff0055; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; margin-left: 10px; animation: pulse 2s infinite; 
        }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
        
        /* Tabs */
        .tabs { display: flex; gap: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); }
        .tab-btn { background: none; border: none; color: var(--text-muted); padding: 10px 0; cursor: pointer; font-size: 1.1rem; position: relative; }
        .tab-btn.active { color: var(--primary); font-weight: bold; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>


    <div class="container">
        <?php if($flash): ?>
            <div style="background: rgba(0,255,136,0.1); padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid var(--primary);">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            
            <!-- Sidebar / Actions -->
            <aside>
                <div class="glass-card">
                    <?php if($role === 'donor'): ?>
                        <!-- Donor Sidebar Content (Same as before) -->
                        <h3 style="margin-bottom: 20px;">🍉 Post Surplus Food</h3>
                        <form method="POST">
                            <?php echo csrfInput(); ?>

                            <div class="form-group">
                                <label class="form-label">Food Title</label>
                                <input type="text" name="title" class="form-input" placeholder="e.g. 20 Veg Meals" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quantity</label>
                                <input type="text" name="quantity" class="form-input" placeholder="e.g. 5kg / 20 packets" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Food Type</label>
                                <select name="food_type" class="form-input" style="background:#111;">
                                    <option value="veg">Vegetarian</option>
                                    <option value="non-veg">Non-Veg</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Expiry Date/Time</label>
                                <input type="datetime-local" name="expiry" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pickup Location</label>
                                <input type="text" name="location" class="form-input" placeholder="Address..." required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-input" rows="3"></textarea>
                            </div>
                            <button type="submit" name="post_food_btn" class="btn btn-primary" style="width:100%">Post Food</button>
                        </form>
                    <?php else: ?>
                        <h3 style="margin-bottom: 20px;">Logistics Stats</h3>
                        <div style="text-align:center; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <h1 style="font-size: 3rem; color: var(--secondary);"><?php echo count($my_logistics); ?></h1>
                            <p style="color:var(--text-muted)">Active Pickups</p>
                        </div>
                        <div style="text-align:center; padding: 20px 0;">
                            <h1 style="font-size: 3rem; color: var(--success);"><?php echo $total_rescued; ?></h1>
                            <p style="color:var(--text-muted)">Total Rescued</p>
                        </div>
                        <div style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted);">
                            <p>✅ Efficient routing saves fuel.</p>
                            <p>✅ Prioritize expiring food.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main>
                <?php if($role === 'donor'): ?>
                    <h2 style="margin-bottom: 20px;">My Listings</h2>
                    <?php foreach($my_listings as $item): ?>
                        <div class="listing-card">
                            <div style="display:flex; justify-content:space-between; align-items:start;">
                                <div>
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p style="color:var(--text-muted); font-size: 0.9rem; margin-top:5px;">
                                        📦 <?php echo htmlspecialchars($item['quantity']); ?> • 
                                        🕑 Exp: <?php echo date('M d, H:i', strtotime($item['expiry_datetime'])); ?>
                                    </p>
                                    <p style="margin-top: 10px;"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                                <span class="status-badge status-<?php echo $item['status']; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </div>
                            <?php if($item['status'] === 'claimed'): 
                                // Find who claimed it and who is delivering
                                $stmt_claim = $pdo->prepare("SELECT u_ngo.id as ngo_id, u_ngo.username as ngo_name, u_vol.id as vol_id, u_vol.username as vol_name 
                                                           FROM claims c 
                                                           JOIN users u_ngo ON c.ngo_id = u_ngo.id 
                                                           LEFT JOIN users u_vol ON c.volunteer_id = u_vol.id 
                                                           WHERE c.listing_id = ?");
                                $stmt_claim->execute([$item['id']]);
                                $claim_info = $stmt_claim->fetch();
                                if($claim_info):
                            ?>
                                <div style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; display: flex; gap: 10px;">
                                    <a href="chat.php?user_id=<?php echo $claim_info['ngo_id']; ?>" class="btn btn-outline" style="font-size: 0.7rem; border-color: var(--secondary); color: var(--secondary);">
                                        💬 Chat NGO (<?php echo htmlspecialchars($claim_info['ngo_name']); ?>)
                                    </a>
                                    <?php if($claim_info['vol_id']): ?>
                                        <a href="chat.php?user_id=<?php echo $claim_info['vol_id']; ?>" class="btn btn-outline" style="font-size: 0.7rem; border-color: var(--primary); color: var(--primary);">
                                            💬 Chat Volunteer (<?php echo htmlspecialchars($claim_info['vol_name']); ?>)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($my_listings)) echo "<p style='color:var(--text-muted)'>No listings yet.</p>"; ?>
                    
                    <!-- Money Donations Section -->
                     <div class="glass-card" style="margin-top: 30px; border-left: 4px solid var(--gold, #ffd700);">
                        <h3 style="color: var(--gold, #ffd700); margin-bottom: 15px;">💰 My Financial Contributions</h3>
                        <ul style="list-style: none;">
                            <?php foreach($my_money_donations as $md): ?>
                                <li style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between;">
                                    <span><?php echo date('M d, Y', strtotime($md['created_at'])); ?></span>
                                    <span style="color: var(--success); font-weight: bold;">$<?php echo number_format($md['amount'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                             <?php if(empty($my_money_donations)): ?>
                                <li style="color: var(--text-muted);">No money donations recorded yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- NGO DASHBOARD -->
                    
                    <h2 style="margin-bottom: 20px;">Operations Center</h2>
                    
                    <!-- NEW: Analytics View -->
                    <div class="glass-card" style="margin-bottom: 30px; padding: 20px;">
                        <h4 style="margin-bottom: 15px; color: var(--text-muted);">📈 Rescue Performance</h4>
                        <canvas id="rescueChart" style="max-height: 250px;"></canvas>
                    </div>

                    
                    <?php if($user['kyc_status'] !== 'approved'): ?>
                        <!-- KYC Block (Same as before) -->
                        <div class="glass-card" style="border: 1px solid var(--warning); background: rgba(245, 158, 11, 0.1);">
                            <h3 style="color: var(--warning);">⚠️ Verification Required</h3>
                            <p style="margin-top: 10px;">Your organization account is currently <strong><?php echo ucfirst($user['kyc_status']); ?></strong>.</p>
                             <?php if($user['kyc_status'] === 'pending' || $user['kyc_status'] === 'rejected'): ?>
                                <hr style="border-color: rgba(255,255,255,0.1); margin: 15px 0;">
                                <form method="POST" enctype="multipart/form-data">
                                    <?php echo csrfInput(); ?>
                                    <div class="form-group">

                                        <label class="form-label">Government ID / NGO Certificate (JPG/PDF)</label>
                                        <input type="file" name="kyc_document" class="form-input" required>
                                    </div>
                                    <button type="submit" name="upload_kyc" class="btn btn-primary">Submit for Verification</button>
                                </form>
                            <?php elseif($user['kyc_status'] === 'submitted'): ?>
                                <div style="margin-top: 15px;">✅ Documents submitted. Waiting for Admin approval.</div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        
                        <!-- VERIFIED NGO VIEW -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                            <div class="stat-card">
                                <div class="stat-icon">🍲</div>
                                <div class="stat-value"><?php echo $total_rescued; ?></div>
                                <div class="stat-label">Parcels Rescued</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">💰</div>
                                <div class="stat-value">Rs. <?php echo number_format($total_received, 0); ?></div>
                                <div class="stat-label">Funds Received</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">🚚</div>
                                <div class="stat-value"><?php echo count($my_logistics); ?></div>
                                <div class="stat-label">Active Logistics</div>
                            </div>
                        </div>

                        <div class="tabs">
                            <button class="tab-btn active" onclick="switchTab('available', event)">Available Food</button>
                            <button class="tab-btn" onclick="switchTab('logistics', event)">My Logistics (<?php echo count($my_logistics); ?>)</button>
                            <button class="tab-btn" onclick="switchTab('financials', event)">Financials</button>
                        </div>

                        <!-- Tab 1: Available Food -->
                        <div id="tab-available" class="tab-content active">
                            <?php foreach($available_food as $item): 
                                $is_urgent = (strtotime($item['expiry_datetime']) < strtotime('+24 hours'));
                            ?>
                                <div class="listing-card">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div>
                                            <h3 style="color: var(--secondary)">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                                <?php if($is_urgent) echo '<span class="badge-urgent">⚡ URGENT</span>'; ?>
                                            </h3>
                                            <p style="color:var(--text-muted); font-size: 0.85rem; margin-bottom: 8px;">
                                                By <strong><?php echo htmlspecialchars($item['donor_name']); ?></strong> • 
                                                📍 <?php echo htmlspecialchars($item['pickup_location']); ?>
                                            </p>
                                            <div style="font-size: 0.9rem;">
                                                📦 <?php echo htmlspecialchars($item['quantity']); ?> • 
                                                🍽️ <?php echo ucfirst($item['food_type']); ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: <?php echo $is_urgent ? '#ff0055' : '#888'; ?>; margin-top: 5px;">
                                                Expires: <?php echo date('M d, H:i', strtotime($item['expiry_datetime'])); ?>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 10px;">
                                            <a href="chat.php?user_id=<?php echo $item['donor_id']; ?>" class="btn btn-outline" style="font-size: 0.8rem;">💬 Chat</a>
                                            <a href="dashboard.php?claim_id=<?php echo $item['id']; ?>" class="btn btn-primary">Claim Now</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if(empty($available_food)) echo "<p style='color:var(--text-muted)'>No food available near you right now.</p>"; ?>
                        </div>

                        <!-- Tab 2: My Logistics -->
                        <div id="tab-logistics" class="tab-content">
                            <?php foreach($my_logistics as $item): ?>
                                <div class="listing-card" style="border-left: 4px solid var(--secondary);">
                                    <div style="display:flex; justify-content:space-between; align-items:start;">
                                        <div>
                                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                            <p style="color:var(--text-muted); font-size: 0.9rem;">
                                                📍 <?php echo htmlspecialchars($item['pickup_location']); ?>
                                            </p>
                                            <div style="margin-top: 10px; font-size: 0.9rem;">
                                                <strong>Donor:</strong> <?php echo htmlspecialchars($item['donor_name']); ?> (<?php echo htmlspecialchars($item['donor_phone'] ?? 'N/A'); ?>)
                                            </div>
                                            <?php if($item['volunteer_name']): ?>
                                                <div style="margin-top: 5px; font-size: 0.9rem; color: var(--secondary);">
                                                    <strong>Transport:</strong> <?php echo htmlspecialchars($item['volunteer_name']); ?> (<?php echo htmlspecialchars($item['volunteer_phone'] ?? 'N/A'); ?>)
                                                    <span style="font-size: 0.75rem; background: rgba(56,189,248,0.1); padding: 2px 6px; border-radius: 4px; margin-left: 5px;">
                                                        <?php echo ucfirst(str_replace('_', ' ', $item['claim_status'])); ?>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <div style="margin-top: 5px; font-size: 0.9rem; color: var(--text-muted);">
                                                    ⌛ Waiting for Volunteer assignment...
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="text-align: right; display: flex; flex-direction: column; gap: 10px;">
                                            <div style="display: flex; gap: 8px;">
                                                <a href="chat.php?user_id=<?php echo $item['donor_id']; ?>" class="btn btn-outline" style="font-size: 0.7rem; padding: 5px 10px;">
                                                    💬 Chat Donor
                                                </a>
                                                <?php if(!empty($item['v_id'])): ?>
                                                    <a href="chat.php?user_id=<?php echo $item['v_id']; ?>" class="btn btn-outline" style="font-size: 0.7rem; padding: 5px 10px; border-color: var(--secondary); color: var(--secondary);">
                                                        💬 Chat Volunteer
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($item['pickup_location']); ?>" target="_blank" class="btn btn-outline" style="font-size: 0.8rem;">
                                                🗺️ Get Directions
                                            </a>
                                            <form method="POST">
                                                <?php echo csrfInput(); ?>
                                                <input type="hidden" name="claim_id" value="<?php echo $item['claim_id']; ?>">
                                                <button type="submit" name="mark_collected" class="btn btn-primary" style="font-size: 0.8rem; background: var(--success); border-color: var(--success);">
                                                    ✅ Mark Collected
                                                </button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if(empty($my_logistics)) echo "<p style='color:var(--text-muted)'>No active pickups. Go claim some food!</p>"; ?>
                        </div>

                         <!-- Tab 3: Financials -->
                        <div id="tab-financials" class="tab-content">
                            <!-- Targeted Donations Section -->
                            <div class="glass-card" style="border-top: 4px solid var(--primary); padding: 20px; margin-bottom: 30px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                                    <h3 style="color: var(--white);">Direct Donations for <?php echo htmlspecialchars($user['username']); ?></h3>
                                    <span style="color: var(--primary); font-weight: bold;">Total: Rs. <?php echo number_format($total_received, 2); ?></span>
                                </div>
                                <ul class="financial-list">
                                    <?php foreach($my_received_donations as $donation): ?>
                                        <li class="financial-item" style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between;">
                                            <div>
                                                <span style="font-weight: bold; color: var(--text-main); display:block;">
                                                    <?php echo $donation['is_anonymous'] ? 'Anonymous Support' : htmlspecialchars($donation['donor_name']); ?>
                                                </span>
                                                <span style="font-size: 0.85rem; color: var(--text-muted);">
                                                    <?php echo date('M d, Y', strtotime($donation['created_at'])); ?>
                                                </span>
                                            </div>
                                            <div style="color: var(--primary); font-weight: bold;">
                                                +Rs. <?php echo number_format($donation['amount'], 2); ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if(empty($my_received_donations)): ?>
                                        <li style="padding: 15px; text-align: center; color: var(--text-muted);">No direct donations received yet. Donors can choose your organization from the donation page!</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <!-- Platform Context Section -->
                            <div class="glass-card" style="border-top: 4px solid var(--gold, #ffd700); padding: 20px; opacity: 0.8;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                                    <h3 style="color: var(--white);">Community Platform Feed</h3>
                                    <button class="btn btn-outline" style="border-color: var(--gold, #ffd700); color: var(--gold, #ffd700); font-size: 0.8rem;">Request Aid from General Fund</button>
                                </div>
                                <ul class="financial-list">
                                    <?php foreach($recent_donations as $donation): ?>
                                        <li class="financial-item" style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; font-size: 0.9rem;">
                                            <div>
                                                <span style="color: var(--text-muted);">Platform Hero</span>
                                            </div>
                                            <div style="color: var(--gold, #ffd700); font-weight: bold;">
                                                Rs. <?php echo number_format($donation['amount'], 0); ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                    <?php endif; ?>
                <?php endif; ?>

            </main>

        </div>
    </div>
    
    <script>
        function switchTab(tabName, event) {
            // Hide all
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            // Show selected
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Initialize Chart.js
        const ctx = document.getElementById('rescueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Rescued Meals',
                        data: [12, 19, 3, 5, 2, 3, 15],
                        borderColor: '#00ff88',
                        backgroundColor: 'rgba(0, 255, 136, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>

</body>
</html>

