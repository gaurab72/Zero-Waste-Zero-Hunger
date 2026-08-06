<?php
session_start();
require_once '../config/db.php';
require_once '../src/functions.php';

// Auth Check
if (!isLoggedIn() || $_SESSION['role'] !== 'volunteer') {
    redirect('login.php');
}

$user = getCurrentUser($pdo);
$flash = getFlash();

// --- ACTION SCRIPTS ---

// 1. Accept Task
if (isset($_GET['accept_claim']) ) {
    $claim_id = (int)$_GET['accept_claim'];
    
    // Check if still available for assignment
    $check = $pdo->prepare("SELECT id FROM claims WHERE id = ? AND volunteer_id IS NULL AND status = 'pending'");
    $check->execute([$claim_id]);
    
    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE claims SET volunteer_id = ?, status = 'assigned' WHERE id = ?");
        $stmt->execute([$user['id'], $claim_id]);
        
        addNotification($pdo, 'Task Assigned', "Volunteer " . $user['username'] . " has accepted a delivery task.", null); // Admin notification
        // Notify NGO who made the claim
        $ngo_id_stmt = $pdo->prepare("SELECT ngo_id FROM claims WHERE id = ?");
        $ngo_id_stmt->execute([$claim_id]);
        $ngo_id = $ngo_id_stmt->fetchColumn();
        addNotification($pdo, 'Task Accepted', "A volunteer has accepted your rescue task.", $ngo_id);

        setFlash('success', 'Task accepted! Please coordinate with the donor.');
    } else {
        setFlash('error', 'Task is no longer available or already assigned.');
    }
    redirect('dashboard_volunteer.php');
}

// 2. Update Status (Picked Up / Delivered)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $claim_id = (int)$_POST['claim_id'];
    $new_status = sanitize($_POST['status']);
    
    // Verify ownership
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirect('dashboard_volunteer.php');
    }

    $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE id = ? AND volunteer_id = ?");
    $stmt->execute([$new_status, $claim_id, $user['id']]);

    
    // If delivered, mark listing as collected
    if ($new_status === 'completed') {
        $stmt = $pdo->prepare("UPDATE food_listings SET status = 'collected' WHERE id = (SELECT listing_id FROM claims WHERE id = ?)");
        $stmt->execute([$claim_id]);
    }
    
    setFlash('success', "Status updated to " . ucfirst($new_status));
    redirect('dashboard_volunteer.php');
}

// 3. Toggle Availability
if (isset($_POST['toggle_availability'])) {
    $current_status = $user['availability_status'];
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';
    
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirect('dashboard_volunteer.php');
    }

    $stmt = $pdo->prepare("UPDATE users SET availability_status = ? WHERE id = ?");
    $stmt->execute([$new_status, $user['id']]);

    
    setFlash('success', "Availability set to " . ucfirst($new_status));
    redirect('dashboard_volunteer.php');
}

// --- DATA FETCHING ---

// 1. Available Tasks (Pending claims from NGOs that need transport)
$stmt = $pdo->query("
    SELECT c.id as claim_id, f.title, f.quantity, f.pickup_location, f.pickup_address, u_ngo.username as ngo_name, u_donor.username as donor_name, u_donor.contact_number as donor_phone
    FROM claims c
    JOIN food_listings f ON c.listing_id = f.id
    JOIN users u_ngo ON c.ngo_id = u_ngo.id
    JOIN users u_donor ON f.donor_id = u_donor.id
    WHERE c.volunteer_id IS NULL AND c.status = 'pending'
");
$available_tasks = $stmt->fetchAll();

// 2. My Active Tasks
$stmt = $pdo->prepare("
    SELECT c.id as claim_id, c.status as delivery_status, f.title, f.quantity, f.pickup_location, f.pickup_address, u_ngo.username as ngo_name, u_ngo.contact_number as ngo_phone, u_donor.username as donor_name, u_donor.contact_number as donor_phone
    FROM claims c
    JOIN food_listings f ON c.listing_id = f.id
    JOIN users u_ngo ON c.ngo_id = u_ngo.id
    JOIN users u_donor ON f.donor_id = u_donor.id
    WHERE c.volunteer_id = ? AND c.status IN ('assigned', 'picked_up')
");
$stmt->execute([$user['id']]);
$my_active_tasks = $stmt->fetchAll();

// 3. Impact Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE volunteer_id = ? AND status = 'completed'");
$stmt->execute([$user['id']]);
$total_deliveries = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard | ZeroWaste</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .task-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .task-card:hover { transform: translateY(-2px); }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge-active { background: rgba(56, 189, 248, 0.1); color: #38bdf8; }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        @media(max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 40px 0;">
        <div class="grid">
            <!-- Stats & Profile -->
            <aside>
                <div class="glass-card">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=00ff88&color=000" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px;">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p style="color: var(--text-muted);">Heroic Volunteer 🦸‍♂️</p>
                    </div>

                    <div style="display: flex; justify-content: space-around; padding: 20px 0; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="text-align: center;">
                            <h2 style="color: var(--primary);"><?php echo $total_deliveries; ?></h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">Delivered</p>
                        </div>
                        <div style="text-align: center;">
                            <h2 style="color: var(--secondary);"><?php echo count($my_active_tasks); ?></h2>
                            <p style="font-size: 0.8rem; color: var(--text-muted);">In Progress</p>
                        </div>
                    </div>

                    <!-- Availability Toggle -->
                    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 10px;">Availability Status (Sewa Mode)</p>
                        <form method="POST">
                            <?php echo csrfInput(); ?>
                            <?php if($user['availability_status'] === 'active'): ?>

                                <button type="submit" name="toggle_availability" class="btn btn-primary" style="width: 100%; position: relative; overflow: hidden;">
                                    <span style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        🟢 Currently Active
                                    </span>
                                </button>
                                <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 8px;">Visible on leaderboard and task board.</p>
                            <?php else: ?>
                                <button type="submit" name="toggle_availability" class="btn btn-outline" style="width: 100%; color: var(--text-muted);">
                                    <span style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        🔴 Currently Inactive
                                    </span>
                                </button>
                                <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 8px;">Hidden from leaderboard. Re-activate when free!</p>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Live Location Tracking -->
                <div class="glass-card" style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;">🌐 Networking & Tech</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 15px;">Coordinate rescues or explore our mission-critical architecture.</p>
                    <a href="directory.php" class="btn btn-primary" style="width: 100%; border-radius: 8px; margin-bottom: 10px;">
                        📂 Browse Directory
                    </a>
                    <a href="professional_experience.php" class="btn btn-outline" style="width: 100%; border-radius: 8px; font-size: 0.8rem; margin-bottom: 15px;">
                        🚀 Technical Showcase
                    </a>
                    <div style="padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <button id="trackLocationBtn" class="btn btn-outline" style="width: 100%; font-size: 0.8rem;">
                            📍 Share Live Location
                        </button>
                    </div>
                </div>
                </div>

                <div class="glass-card" style="margin-top: 20px; background: rgba(0, 255, 136, 0.05);">
                    <h4 style="color: var(--primary); margin-bottom: 15px;">Safety Mission</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
                        Your work bridges the gap between surplus and hunger. Always verify the quality of food during pickup.
                    </p>
                </div>
            </aside>

            <!-- Deliveries Management -->
            <main>
                <?php if($flash): ?>
                    <div style="background: rgba(0,255,136,0.1); padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid var(--primary);">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <h2 style="margin-bottom: 30px;">Active Deliveries</h2>
                <?php foreach($my_active_tasks as $task): ?>
                    <div class="task-card" style="border-left: 4px solid var(--secondary);">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <span class="badge badge-active" style="margin-bottom: 10px; display: inline-block;">
                                    <?php echo ucfirst(str_replace('_', ' ', $task['delivery_status'])); ?>
                                </span>
                                <h3><?php echo htmlspecialchars($task['title']); ?> (<?php echo $task['quantity']; ?>)</h3>
                                <p style="margin: 10px 0; font-size: 0.95rem;">
                                    <strong>From:</strong> <?php echo htmlspecialchars($task['donor_name']); ?> (<?php echo $task['donor_phone']; ?>)<br>
                                    <strong>To:</strong> <?php echo htmlspecialchars($task['ngo_name']); ?> (<?php echo $task['ngo_phone']; ?>)
                                </p>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">
                                    📍 <?php echo htmlspecialchars($task['pickup_address']); ?>
                                </p>
                            </div>
                            <div style="text-align: right; display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: flex; gap: 8px;">
                                    <a href="chat.php?user_id=<?php echo $task['donor_id']; ?>" class="btn btn-outline" style="font-size: 0.7rem; padding: 5px 10px;">💬 Chat Donor</a>
                                    
                                    <a href="chat.php?user_id=<?php echo $task['ngo_id']; ?>" class="btn btn-outline" style="font-size: 0.7rem; padding: 5px 10px; border-color: var(--secondary); color: var(--secondary);">💬 Chat NGO</a>
                                </div>

                                <form method="POST">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="claim_id" value="<?php echo $task['claim_id']; ?>">
                                    <?php if($task['delivery_status'] === 'assigned'): ?>

                                        <input type="hidden" name="status" value="picked_up">
                                        <button type="submit" name="update_status" class="btn btn-primary" style="font-size: 0.8rem;">Mark as Picked Up</button>
                                    <?php else: ?>
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" name="update_status" class="btn btn-primary" style="background: #10b981; font-size: 0.8rem;">Mark as Delivered</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($my_active_tasks)) echo "<p style='color: var(--text-muted); margin-bottom: 40px;'>No active tasks. Check available tasks below.</p>"; ?>

                <h2 style="margin-bottom: 30px;">Available Task Board</h2>
                <?php foreach($available_tasks as $task): ?>
                    <div class="task-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="color: var(--primary);"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <p style="margin: 5px 0;">For: <strong><?php echo htmlspecialchars($task['ngo_name']); ?></strong></p>
                                <p style="color: var(--text-muted); font-size: 0.9rem;">📍 <?php echo htmlspecialchars($task['pickup_location']); ?></p>
                            </div>
                            <a href="dashboard_volunteer.php?accept_claim=<?php echo $task['claim_id']; ?>" class="btn btn-outline">Accept Task</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($available_tasks)) echo "<p style='color: var(--text-muted)'>No new delivery requests at the moment.</p>"; ?>
            </main>
        </div>
    </div>
    <script>
        function updateMyLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    fetch('api/update_location.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        }),
                        headers: { 'Content-Type': 'application/json' }
                    });
                });
            }
        }

        let trackingInterval = null;
        const trackBtn = document.getElementById('trackLocationBtn');
        if (trackBtn) {
            trackBtn.addEventListener('click', function() {
                if (!trackingInterval) {
                    trackingInterval = setInterval(updateMyLocation, 10000);
                    updateMyLocation();
                    this.innerHTML = "🛑 Stop & Clear Location";
                    this.classList.add('btn-danger');
                    this.style.color = "white";
                    this.style.background = "#ef4444";
                } else {
                    clearInterval(trackingInterval);
                    trackingInterval = null;

                    // Clear from DB
                    fetch('api/update_location.php', {
                        method: 'POST',
                        body: JSON.stringify({ action: 'clear' }),
                        headers: { 'Content-Type': 'application/json' }
                    });

                    this.innerHTML = "📍 Share Live Location";
                    this.classList.remove('btn-danger');
                    this.style.color = "";
                    this.style.background = "";
                }
            });
        }
    </script>
</body>
</html>

