<?php
// public/professional_experience.php
require_once '../config/db.php';
require_once '../src/functions.php';
session_start();

// Fetch some real stats for the showcase
$total_coordinations = $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'completed'")->fetchColumn();
$verified_partners = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('donor', 'ngo') AND kyc_status = 'approved'")->fetchColumn();
$active_volunteers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer'")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technical Showcase | Professional Experience | ZeroWaste</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .showcase-header {
            padding: 100px 0 60px;
            text-align: center;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }
        .feature-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 24px;
            position: relative;
            transition: 0.3s;
        }
        .feature-box:hover {
            border-color: var(--primary);
            background: rgba(0, 255, 136, 0.02);
        }
        .tech-tag {
            background: rgba(255, 255, 255, 0.05);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            color: var(--primary);
            font-family: monospace;
            margin-right: 8px;
            display: inline-block;
            margin-top: 15px;
        }
        .architecture-diagram {
            background: #000;
            border-radius: 24px;
            padding: 50px;
            border: 1px solid var(--glass-border);
            margin-bottom: 80px;
            text-align: center;
        }
        .stat-banner {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 60px;
        }
        .stat-panel {
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid var(--glass-border);
        }
        .stat-num { font-size: 3rem; font-weight: 800; color: var(--primary); margin-bottom: 10px; }
        .stat-desc { color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <header class="showcase-header">
            <h1 class="text-gradient" style="font-size: 4rem;">Technical Experience</h1>
            <p style="font-size: 1.25rem; color: var(--text-muted); margin-top: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
                Engineering a sustainable future through decentralized food rescue coordination. 
                Our platform bridges the gap between surplus and survival using state-of-the-art infrastructure.
            </p>
        </header>

        <section class="stat-banner">
            <div class="stat-panel">
                <div class="stat-num"><?php echo $total_coordinations; ?>+</div>
                <div class="stat-desc">Successful Rescues</div>
            </div>
            <div class="stat-panel">
                <div class="stat-num"><?php echo $verified_partners; ?></div>
                <div class="stat-desc">Verified Mission Partners</div>
            </div>
            <div class="stat-panel">
                <div class="stat-num"><?php echo $active_volunteers; ?></div>
                <div class="stat-desc">Logistics Legends</div>
            </div>
        </section>

        <h2 style="margin-bottom: 40px; text-align: center;">Advanced Capabilities</h2>
        <div class="feature-grid">
            <div class="feature-box">
                <div style="font-size: 3rem; margin-bottom: 25px;">🛰️</div>
                <h3>Dynamic Coordination Engine</h3>
                <p style="color: var(--text-muted); line-height: 1.6; margin-top: 15px;">
                    Implemented a real-time availability system that monitors donor surplus and NGO capacity. 
                    Volunteers use a high-performance directory to proactively bridge logistics gaps before food expires.
                </p>
                <div>
                    <span class="tech-tag">Dynamic SQL</span>
                    <span class="tech-tag">Subquery Optimizations</span>
                    <span class="tech-tag">Real-time State Tracking</span>
                </div>
            </div>

            <div class="feature-box">
                <div style="font-size: 3rem; margin-bottom: 25px;">💬</div>
                <h3>Secure Mission Messaging</h3>
                <p style="color: var(--text-muted); line-height: 1.6; margin-top: 15px;">
                    A decentralized communication layer allowing direct coordination between three distinct roles. 
                    Features instant notification triggers and mission-critical message persistence.
                </p>
                <div>
                    <span class="tech-tag">Role-Based Access Control</span>
                    <span class="tech-tag">Persistence Layer</span>
                    <span class="tech-tag">Notification Logic</span>
                </div>
            </div>

            <div class="feature-box">
                <div style="font-size: 3rem; margin-bottom: 25px;">📍</div>
                <h3>Live Geolocation Stack</h3>
                <p style="color: var(--text-muted); line-height: 1.6; margin-top: 15px;">
                    Volunteer transport safety and efficiency are managed via a live GPS tracking system. 
                    Integrating Leaflet.js with a backend location API for transparent food movement.
                </p>
                <div>
                    <span class="tech-tag">Leaflet JS</span>
                    <span class="tech-tag">Geo-JSON</span>
                    <span class="tech-tag">RESTful API</span>
                </div>
            </div>
        </div>

        <section class="architecture-diagram">
            <h2 style="margin-bottom: 30px;">Coordination Architecture</h2>
            <div style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 40px; color: var(--text-muted);">
                <div style="border: 1px solid var(--primary); padding: 30px; border-radius: 20px; width: 220px;">
                    <h4 style="color: var(--primary);"> donors </h4>
                    <p style="font-size: 0.8rem; margin-top: 10px;">Food Surplus <br> Publication Layer</p>
                </div>
                <div style="font-size: 2rem;">⚡</div>
                <div style="border: 1px solid var(--secondary); padding: 30px; border-radius: 20px; width: 220px; background: rgba(56, 189, 248, 0.05);">
                    <h4 style="color: var(--secondary);"> volunteers </h4>
                    <p style="font-size: 0.8rem; margin-top: 10px;">Logistics & <br> Coordination Layer</p>
                </div>
                <div style="font-size: 2rem;">⚡</div>
                <div style="border: 1px solid #10b981; padding: 30px; border-radius: 20px; width: 220px;">
                    <h4 style="color: #10b981;"> ngos </h4>
                    <p style="font-size: 0.8rem; margin-top: 10px;">Needs & <br> Distribution Layer</p>
                </div>
            </div>
            <p style="margin-top: 50px; color: var(--text-muted);">
                A triangle-of-trust architecture ensuring no resource goes to waste.
            </p>
        </section>

        <div style="text-align: center; margin-bottom: 100px;">
            <a href="about.php" class="btn btn-outline" style="margin-right: 15px;">← Back to Mission</a>
            <a href="register.php" class="btn btn-primary">Join the Network</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
