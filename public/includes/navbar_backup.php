<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure core dependencies are loaded for all pages using the navbar
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/functions.php';

// specific helper to get active class
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'color:var(--primary); font-weight:bold;' : '';
}
?>

<link rel="stylesheet" href="assets/css/3d_logo.css">
<nav class="navbar container">
    <a href="index.php" class="logo logo-3d-container" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
        <img src="assets/images/admin_logo_3d.gif" alt="Logo" class="logo-3d nav-logo-3d" style="height: 45px; border-radius: 5px;">
        <span style="font-weight: 800; font-size: 1.2rem; color: var(--text-main);">
            ZeroWaste-<span style="color:var(--primary)">ZeroHunger</span>
        </span>
    </a>
    
    <div class="nav-links">
        <!-- Main Navigation -->
        <a href="index.php" style="<?php echo isActive('index.php'); ?>">Home</a>
        <a href="about.php" style="<?php echo isActive('about.php'); ?>">About Us</a>
        <a href="impact.php" style="<?php echo isActive('impact.php'); ?>">Our Impact</a>
        <a href="leaderboard.php" style="<?php echo isActive('leaderboard.php'); ?>">Leaderboard 🏆</a>
        <a href="feedback.php" style="<?php echo isActive('feedback.php'); ?>">Feedback 💬</a>
        
        <!-- Action Group -->
        <div style="width: 1px; height: 24px; background: var(--glass-border); margin: 0 10px;"></div>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'donor'): ?>
            <a href="donate.php" class="btn btn-primary" style="background: linear-gradient(135deg, #ffd700, #f59e0b); border:none; color: #000; font-weight:bold; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);">
                Donate Now
            </a>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['user_id'])): 
            $unread_count = getUnreadCount($pdo);
        ?>

            <!-- Notifications & Chat -->
            <a href="chat.php" style="position: relative; margin-left: 20px;" title="Messages">
                💬
                <span style="position: absolute; top: -8px; right: -8px; background: var(--secondary); color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 50%; display: <?php echo $unread_count > 0 ? 'inline' : 'none'; ?>;">
                    <?php echo $unread_count; ?>
                </span>
            </a>
            
            <!-- User Menu -->
            <div style="position: relative; display: inline-block; margin-left: 15px;">
                <a href="<?php 
                    if($_SESSION['role'] === 'admin') echo 'admin/dashboard.php';
                    elseif($_SESSION['role'] === 'volunteer') echo 'dashboard_volunteer.php';
                    else echo 'dashboard.php';
                   ?>" 
                   style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                   <span style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                   <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=10b981&color=fff" 
                        style="width: 32px; height: 32px; border-radius: 50%;">
                </a>
            </div>

            <?php if($_SESSION['role'] === 'volunteer'): ?>
                <a href="directory.php" style="<?php echo isActive('directory.php'); ?> margin-left: 15px;" title="Contact Directory">📂 Directory</a>
            <?php endif; ?>

            <a href="../src/auth.php?logout=true" style="color: var(--danger); font-size: 0.9rem; margin-left: 10px;">Logout</a>
            
        <?php else: ?>

            <!-- Guest Actions -->
            <a href="login.php" style="font-weight: 600;">Sign In</a>
            <a href="register.php" class="btn btn-outline" style="border-width: 2px;">Join Mission</a>
        <?php endif; ?>
        
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode" style="margin-left: 10px;">
        </button>
    </div>
</nav>
<script src="assets/js/theme.js"></script>

<!-- Include Sathi Chatbot Widget -->
<?php include_once 'sathi_widget.php'; ?>
