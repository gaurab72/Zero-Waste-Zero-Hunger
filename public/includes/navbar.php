<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/functions.php';

function isActive($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'nav-active' : '';
}
?>

<!-- ===== NAVBAR STYLESHEET ===== -->
<style>
.site-navbar-wrap {
    position: sticky;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    background: rgba(10,14,10,0.88);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid rgba(255,255,255,0.07);
    box-shadow: 0 2px 24px rgba(0,0,0,0.35);
}
.site-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
    height: 64px;
    gap: 16px;
}
.snb-logo { display:flex; align-items:center; gap:10px; text-decoration:none; flex-shrink:0; }
.snb-logo img { height:42px; border-radius:6px; object-fit:contain; }
.snb-logo-text { font-size:.9rem; font-weight:800; line-height:1.25; color:#fff; text-transform:uppercase; letter-spacing:.5px; }
.snb-logo-text em { font-style:normal; color:#10b981; }
.snb-links { display:flex; align-items:center; gap:2px; list-style:none; margin:0; padding:0; flex:1; justify-content:center; }
.snb-links li a { position:relative; display:inline-flex; align-items:center; gap:5px; padding:6px 13px; font-size:.93rem; font-weight:500; color:rgba(255,255,255,.70); text-decoration:none; border-radius:8px; transition:color .2s,background .2s; white-space:nowrap; }
.snb-links li a:hover { color:#fff; background:rgba(255,255,255,.07); }
.snb-links li a.nav-active { color:#10b981; font-weight:700; }
.snb-links li a.nav-active::after { content:''; position:absolute; bottom:0; left:13px; right:13px; height:2px; border-radius:2px; background:#10b981; }
.snb-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.snb-signin { font-size:.92rem; font-weight:600; color:rgba(255,255,255,.80); text-decoration:none; padding:7px 14px; border-radius:9px; transition:color .2s,background .2s; }
.snb-signin:hover { color:#fff; background:rgba(255,255,255,.07); }
.snb-cta { display:inline-flex; align-items:center; padding:8px 20px; background:transparent; border:2px solid #10b981; color:#10b981; font-size:.9rem; font-weight:700; border-radius:10px; text-decoration:none; transition:background .25s,color .25s,box-shadow .25s; white-space:nowrap; }
.snb-cta:hover { background:#10b981; color:#000; box-shadow:0 0 18px rgba(16,185,129,.45); }
.snb-donate { display:inline-flex; align-items:center; gap:5px; padding:8px 16px; background:linear-gradient(135deg,#ffd700,#f59e0b); border:none; color:#000; font-size:.9rem; font-weight:700; border-radius:10px; text-decoration:none; transition:transform .2s,box-shadow .2s; }
.snb-donate:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(255,215,0,.35); }
.snb-theme-btn { width:34px; height:34px; border-radius:50%; border:1px solid rgba(255,255,255,.15); background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:1rem; transition:background .2s,border-color .2s; }
.snb-theme-btn:hover { background:rgba(255,255,255,.10); border-color:rgba(255,255,255,.30); }
.snb-divider { width:1px; height:22px; background:rgba(255,255,255,.12); }
.snb-hamburger { width:34px; height:34px; border-radius:8px; border:1px solid rgba(255,255,255,.12); background:transparent; cursor:pointer; display:none; flex-direction:column; align-items:center; justify-content:center; gap:5px; padding:6px; }
.snb-hamburger span { width:18px; height:2px; background:rgba(255,255,255,.75); border-radius:2px; transition:all .3s; }
.snb-badge-wrap { position:relative; text-decoration:none; font-size:1.1rem; padding:5px 7px; border-radius:8px; transition:background .2s; }
.snb-badge-wrap:hover { background:rgba(255,255,255,.08); }
.snb-badge { position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; font-size:.6rem; font-weight:700; padding:1px 5px; border-radius:999px; min-width:16px; text-align:center; }
.snb-user-pill { display:flex; align-items:center; gap:8px; text-decoration:none; padding:4px 12px 4px 4px; border-radius:999px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.04); max-width:190px; transition:background .2s; }
.snb-user-pill:hover { background:rgba(255,255,255,.09); }
.snb-user-pill img { width:28px; height:28px; border-radius:50%; flex-shrink:0; }
.snb-user-pill span { font-size:.84rem; font-weight:600; color:rgba(255,255,255,.88); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.snb-logout { font-size:.82rem; color:#ef4444; text-decoration:none; padding:6px 10px; border-radius:8px; font-weight:600; transition:background .2s; }
.snb-logout:hover { background:rgba(239,68,68,.12); }
@media(max-width:960px) {
    .snb-links { display:none; }
    .snb-hamburger { display:flex; }
    .snb-signin, .snb-cta { display:none; }
}
</style>

<link rel="stylesheet" href="assets/css/3d_logo.css">
<div class="site-navbar-wrap">
    <nav class="site-navbar" aria-label="Main navigation">

        <!-- LOGO (original GIF preserved) -->
        <a href="index.php" class="snb-logo">
            <img src="assets/Images/admin_logo_3d.gif" alt="ZeroWaste-ZeroHunger Logo" class="logo-3d nav-logo-3d">
            <span class="snb-logo-text">ZeroWaste-<br><em>ZeroHunger</em></span>
        </a>

        <!-- CENTRE NAV LINKS -->
        <ul class="snb-links">
            <li><a href="index.php"       class="<?php echo isActive('index.php'); ?>">Home</a></li>
            <li><a href="about.php"       class="<?php echo isActive('about.php'); ?>">About Us</a></li>
            <li><a href="impact.php"      class="<?php echo isActive('impact.php'); ?>">Our Impact</a></li>
            <li><a href="leaderboard.php" class="<?php echo isActive('leaderboard.php'); ?>">Leaderboard &#127942;</a></li>
            <li><a href="feedback.php"    class="<?php echo isActive('feedback.php'); ?>">Feedback</a></li>
        </ul>

        <!-- RIGHT ACTION GROUP -->
        <div class="snb-actions">

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'donor'): ?>
                <a href="donate.php" class="snb-donate">&#128176; Donate Now</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])):
                $unread_count = getUnreadCount($pdo); ?>

                <a href="chat.php" class="snb-badge-wrap" title="Messages">
                    &#128172;
                    <?php if ($unread_count > 0): ?>
                        <span class="snb-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>

                <?php if ($_SESSION['role'] === 'volunteer'): ?>
                    <a href="directory.php" class="snb-signin">&#128194; Directory</a>
                <?php endif; ?>

                <a href="<?php
                    if ($_SESSION['role'] === 'admin')         echo 'admin/dashboard.php';
                    elseif ($_SESSION['role'] === 'volunteer') echo 'dashboard_volunteer.php';
                    else                                        echo 'dashboard.php';
                ?>" class="snb-user-pill">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=10b981&color=fff" alt="User avatar">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>

                <a href="../src/auth.php?logout=true" class="snb-logout">Logout</a>

            <?php else: ?>
                <a href="login.php"    class="snb-signin">Sign In</a>
                <a href="register.php" class="snb-cta">Join Mission</a>
            <?php endif; ?>

            <div class="snb-divider"></div>

            <button id="theme-toggle" class="snb-theme-btn" aria-label="Toggle dark mode">&#9728;&#65039;</button>

            <button class="snb-hamburger" id="snb-hamburger-btn" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
</div>

<script>
(function(){
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    var saved = localStorage.getItem('theme') || 'dark';
    btn.innerHTML = saved === 'dark' ? '&#9728;&#65039;' : '&#127769;';
})();
</script>
<script src="assets/js/theme.js"></script>

