<?php
// public/directory.php
require_once '../config/db.php';
require_once '../src/functions.php';
session_start();

// Auth Check - Only volunteers can access the full directory
if (!isLoggedIn() || $_SESSION['role'] !== 'volunteer') {
    redirect('login.php');
}

$user = getCurrentUser($pdo);
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Enhanced Query with Dynamic Availability
$query = "
    SELECT u.id, u.username, u.email, u.role, u.location, u.contact_number,
           (CASE 
                WHEN u.role = 'donor' THEN (SELECT COUNT(*) FROM food_listings WHERE donor_id = u.id AND status = 'available')
                WHEN u.role = 'ngo' THEN (SELECT COUNT(*) FROM claims WHERE ngo_id = u.id AND status IN ('pending', 'assigned'))
                ELSE 0 
            END) as active_missions
    FROM users u 
    WHERE u.role IN ('donor', 'ngo') AND u.kyc_status = 'approved'
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.username LIKE ? OR u.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($role_filter)) {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
}

$query .= " ORDER BY active_missions DESC, u.username ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$contacts = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Coordination Directory | ZeroWaste</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .directory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .contact-card {
            background: var(--bg-panel);
            border: 1px solid var(--glass-border);
            padding: 24px;
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .contact-card:hover { 
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.1);
        }
        .contact-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            transform: translateX(-100%);
            transition: 0.6s;
        }
        .contact-card:hover::before { transform: translateX(100%); }

        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .badge-donor { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); }
        .badge-ngo { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        
        .availability-glow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: var(--primary);
            font-weight: 600;
            margin-top: 10px;
        }
        .pulse-dot {
            width: 8px; height: 8px;
            background: var(--primary);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--primary);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(0, 255, 136, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 255, 136, 0); }
        }

        .search-bar {
            display: flex;
            gap: 15px;
            background: rgba(255,255,255,0.02);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            margin-bottom: 40px;
            flex-wrap: wrap;
            backdrop-filter: blur(10px);
        }
        .search-input { flex: 1; min-width: 250px; background: rgba(0,0,0,0.2) !important; }
        .filter-select { min-width: 180px; background: rgba(0,0,0,0.2) !important; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 60px 0;">
        <div style="margin-bottom: 50px; text-align: center;">
            <h1 class="text-gradient" style="font-size: 3rem;">Coordination Hub</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 700px; margin: 15px auto;">
                Connect directly with our network of heroes. Proactive communication saves lives and reduces waste.
            </p>
        </div>

        <!-- Search & Filter -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-input search-input" placeholder="🔍 Search Donor/NGO name or district..." value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="role" class="form-input filter-select">
                <option value="">All Mission Partners</option>
                <option value="donor" <?php echo $role_filter === 'donor' ? 'selected' : ''; ?>>Food Donors</option>
                <option value="ngo" <?php echo $role_filter === 'ngo' ? 'selected' : ''; ?>>Welfare NGOs</option>
            </select>

            <button type="submit" class="btn btn-primary" style="width: auto; padding: 12px 30px;">Filter Heroes</button>
            <a href="directory.php" class="btn btn-outline" style="width: auto;">Reset</a>
        </form>

        <!-- Directory Grid -->
        <div class="directory-grid">
            <?php foreach($contacts as $c): ?>
                <div class="contact-card">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                            <div style="position: relative;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c['username']); ?>&background=random" style="width: 64px; height: 64px; border-radius: 16px;">
                                <?php if($c['active_missions'] > 0): ?>
                                    <div style="position: absolute; bottom: -5px; right: -5px; background: var(--primary); border: 2px solid var(--bg-panel); width: 14px; height: 14px; border-radius: 50%;"></div>
                                <?php endif; ?>
                            </div>
                            <span class="role-badge <?php echo $c['role'] === 'donor' ? 'badge-donor' : 'badge-ngo'; ?>">
                                <?php echo htmlspecialchars($c['role']); ?>
                            </span>
                        </div>
                        
                        <h3 style="margin-bottom: 8px; font-size: 1.3rem;"><?php echo htmlspecialchars($c['username']); ?></h3>
                        <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 5px;">
                            📍 <?php echo htmlspecialchars($c['location'] ?: 'Unspecified Location'); ?>
                        </p>
                        
                        <?php if($c['active_missions'] > 0): ?>
                            <div class="availability-glow">
                                <span class="pulse-dot"></span>
                                <?php echo $c['role'] === 'donor' ? 'Food Available for Pickup' : 'Actively Rescuing'; ?>
                            </div>
                        <?php else: ?>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; background: rgba(255,255,255,0.1); border-radius: 50%;"></span>
                                Standby Mode
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; margin-top: 25px;">
                        <a href="chat.php?user_id=<?php echo $c['id']; ?>" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 0.95rem;">
                            💬 Coordinate Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($contacts)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: rgba(255,255,255,0.01); border-radius: 30px; border: 2px dashed rgba(255,255,255,0.05);">
                    <div style="font-size: 4rem; margin-bottom: 25px; opacity: 0.5;">🛰️</div>
                    <h2 style="color: var(--text-muted);">No Heroes Found in Orbit</h2>
                    <p style="color: var(--text-muted); margin-top: 10px;">Try broadening your coordination search criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
