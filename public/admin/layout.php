<?php
// public/admin/layout.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../config/db.php';
require_once '../../src/functions.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // redirect('../admin_login.php'); // Uncomment in prod
}

$page_title = isset($page_title) ? $page_title : 'Executive Overview';
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin Khan';
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : 'admin@zerowaste.np';

// Initials for avatar
$name_parts = explode(' ', $admin_name);
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : 'K'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | ZeroWaste Admin</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Global Style & Theme System -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Admin Specifics -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <!-- Brand Header (Keeps Original 3D Logo) -->
        <a href="dashboard.php" class="brand">
            <img src="../assets/Images/admin_logo_3d.gif" alt="ZeroWaste Logo" class="brand-logo-img">
            <div class="brand-text-wrapper">
                <span class="brand-main-title">ZeroWaste</span>
                <span class="brand-sub-title">ZeroHunger</span>
            </div>
        </a>
        
        <div class="sidebar-section-label">MAIN MENU</div>

        <nav class="sidebar-nav">
            <!-- Dashboard -->
            <a href="dashboard.php" class="nav-item <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <div class="nav-item-left">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                    <span>Dashboard</span>
                </div>
                <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>

            <!-- Users & KYC -->
            <a href="users.php" class="nav-item <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                <div class="nav-item-left">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Users & KYC</span>
                </div>
                <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>

            <!-- Food Requests -->
            <a href="requests.php" class="nav-item <?php echo $current_page === 'requests.php' ? 'active' : ''; ?>">
                <div class="nav-item-left">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span>Food Requests</span>
                </div>
                <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>

            <!-- Donations -->
            <a href="donations.php" class="nav-item <?php echo $current_page === 'donations.php' ? 'active' : ''; ?>">
                <div class="nav-item-left">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <span>Donations</span>
                </div>
                <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>

            <!-- Analytics -->
            <a href="reports.php" class="nav-item <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                <div class="nav-item-left">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span>Analytics</span>
                </div>
                <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>

            <!-- Feedback -->
            <a href="feedback.php" class="nav-item <?php echo $current_page === 'feedback.php' ? 'active' : ''; ?>">
                <div class="nav-item-left">
                    <svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Feedback</span>
                </div>
                <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        </nav>

        <!-- Sidebar Profile Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-user-wrapper">
                <div class="user-avatar-circle">
                    <?php echo $initials; ?>
                </div>
                <div class="user-info-text">
                    <span class="user-name-text"><?php echo htmlspecialchars($admin_name); ?></span>
                    <span class="user-email-text"><?php echo htmlspecialchars($admin_email); ?></span>
                </div>
            </div>
            <a href="../../src/auth.php?logout=true" class="logout-btn-icon" title="Logout">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-title">
                <h2><?php echo htmlspecialchars($page_title); ?></h2>
                <p class="top-bar-subtitle">System performance & impact — <?php echo date('F j, Y'); ?></p>
            </div>

            <div class="top-bar-right">
                <!-- Search Box -->
                <div class="search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" placeholder="Search..." aria-label="Search">
                </div>

                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode" style="margin:0; width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.03); border:1px solid var(--admin-card-border); justify-content:center;">
                    <!-- Icon Injected by JS -->
                </button>
                
                <?php 
                    $unread_count = getUnreadCount($pdo); 
                    $notifications = getUnreadNotifications($pdo, 5);
                ?>
                <div class="notification-wrapper" style="position: relative;">
                    <div style="position: relative; cursor: pointer; width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.03); border:1px solid var(--admin-card-border); display:flex; align-items:center; justify-content:center;" onclick="toggleNotifications()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <?php if($unread_count > 0): ?>
                            <span id="notif-badge" style="position: absolute; top:-4px; right:-4px; background:var(--accent-danger); color:white; border-radius:50%; width:18px; height:18px; font-size:10px; font-weight:bold; display:flex; align-items:center; justify-content:center; border: 2px solid var(--admin-bg);">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Notification Dropdown -->
                    <div id="notif-dropdown" style="display:none; position: absolute; right: 0; top: 48px; width: 320px; background: var(--admin-card-bg); border: 1px solid var(--admin-card-border); border-radius: 14px; box-shadow: 0 12px 30px rgba(0,0,0,0.5); z-index: 1000; overflow: hidden;">
                        <div style="padding: 15px; border-bottom: 1px solid var(--admin-card-border); display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:600; font-size:0.9rem;">Notifications</span>
                            <?php if($unread_count > 0): ?>
                                <button onclick="markAllRead()" style="background:none; border:none; color:var(--primary-green-bright); font-size:0.75rem; cursor:pointer; font-weight:600;">Mark all read</button>
                            <?php endif; ?>
                        </div>
                        <div id="notif-list" style="max-height: 300px; overflow-y: auto;">
                            <?php if(empty($notifications)): ?>
                                <div style="padding: 20px; text-align: center; color: var(--text-dim); font-size: 0.85rem;">
                                    No new notifications
                                </div>
                            <?php else: ?>
                                <?php foreach($notifications as $notif): ?>
                                    <div class="notif-item" style="padding: 12px 15px; border-bottom: 1px solid var(--admin-card-border); cursor:pointer;" onclick="markRead(<?php echo $notif['id']; ?>, this)">
                                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                            <span style="font-size:0.75rem; font-weight:700; color:var(--primary-green-bright);"><?php echo htmlspecialchars($notif['type']); ?></span>
                                            <span style="font-size:0.7rem; color:var(--text-dim);"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></span>
                                        </div>
                                        <div style="font-size:0.85rem; color:var(--text-primary); line-height:1.4;">
                                            <?php echo htmlspecialchars($notif['message']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sleek Admin Profile Badge displaying Name & Email -->
                <a href="profile.php" class="topbar-admin-badge" title="View Admin Profile">
                    <img src="../assets/Images/admin_logo_3d.gif" alt="Admin Avatar" class="topbar-admin-avatar">
                    <div class="topbar-admin-details">
                        <span class="topbar-admin-name"><?php echo htmlspecialchars($admin_name); ?></span>
                        <span class="topbar-admin-email"><?php echo htmlspecialchars($admin_email); ?></span>
                    </div>
                    <span class="topbar-online-dot" title="Active Admin Session"></span>
                </a>
            </div>
        </div>

        <script>
            function toggleNotifications() {
                const drop = document.getElementById('notif-dropdown');
                drop.style.display = drop.style.display === 'none' ? 'block' : 'none';
            }

            document.addEventListener('click', function(e) {
                const notifWrapper = document.querySelector('.notification-wrapper');
                if (notifWrapper && !notifWrapper.contains(e.target)) {
                    const drop = document.getElementById('notif-dropdown');
                    if (drop) drop.style.display = 'none';
                }
            });

            function markRead(id, el) {
                fetch('mark_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        el.style.opacity = '0.5';
                        setTimeout(() => el.remove(), 300);
                        updateBadge(-1);
                    }
                });
            }

            function markAllRead() {
                fetch('mark_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'mark_all' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const list = document.getElementById('notif-list');
                        list.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-dim); font-size: 0.85rem;">No new notifications</div>';
                        const badge = document.getElementById('notif-badge');
                        if (badge) badge.remove();
                    }
                });
            }

            function updateBadge(change) {
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    let count = parseInt(badge.innerText) + change;
                    if (count <= 0) {
                        badge.remove();
                    } else {
                        badge.innerText = count;
                    }
                }
            }
        </script>
        
        <!-- Flash Messages -->
        <?php if($flash = getFlash()): ?>
            <div style="padding: 14px 20px; border-radius: 12px; margin-bottom: 25px; background: <?php echo $flash['type'] == 'error' ? 'rgba(239,68,68,0.12)' : 'rgba(16,185,129,0.12)'; ?>; border: 1px solid <?php echo $flash['type'] == 'error' ? 'var(--accent-danger)' : 'var(--primary-green)'; ?>; color: <?php echo $flash['type'] == 'error' ? 'var(--accent-danger)' : 'var(--primary-green-bright)'; ?>; font-size: 0.9rem; font-weight: 500;">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Script to load theme logic -->
        <script src="../assets/js/theme.js"></script>
