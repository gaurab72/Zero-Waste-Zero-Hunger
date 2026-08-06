<?php
// Session and dependencies are handled by navbar.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroWaste-ZeroHunger | Sustainable Food Rescue & Sewa Network</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/3d_logo.css">
</head>
<body>


    <div id="canvas-container"></div>

<?php
// Database and Helpers
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/functions.php';

// Fetch Data for Home Page Display
try {
    $home_top_donors = $pdo->query("SELECT * FROM money_donations WHERE is_anonymous = 0 ORDER BY amount DESC LIMIT 3")->fetchAll();
    $total_money_raised = $pdo->query("SELECT SUM(amount) FROM money_donations")->fetchColumn() ?: 0;
    
    $home_top_food = $pdo->query("
        SELECT u.username as donor_name, COUNT(f.id) as donation_count 
        FROM food_listings f 
        JOIN users u ON f.donor_id = u.id 
        GROUP BY f.donor_id 
        ORDER BY donation_count DESC 
        LIMIT 3
    ")->fetchAll();

    $home_top_volunteers = $pdo->query("
        SELECT u.username as volunteer_name, COUNT(c.id) as delivery_count 
        FROM users u 
        LEFT JOIN claims c ON u.id = c.volunteer_id AND c.status = 'completed'
        WHERE u.role = 'volunteer'
        GROUP BY u.id 
        ORDER BY delivery_count DESC 
        LIMIT 3
    ")->fetchAll();

    // Fetch active available food listings for live feed
    $active_food_feed = $pdo->query("
        SELECT f.*, u.username as donor_name 
        FROM food_listings f 
        JOIN users u ON f.donor_id = u.id 
        WHERE f.status = 'available' AND f.expiry_datetime > NOW()
        ORDER BY f.created_at DESC 
        LIMIT 3
    ")->fetchAll();

} catch (PDOException $e) {
    $home_top_donors = [];
    $total_money_raised = 0;
    $home_top_food = [];
    $home_top_volunteers = [];
    $active_food_feed = [];
    error_log("Home Stats Error: " . $e->getMessage());
}
?>

    <!-- Live Alert Marquee Strip -->
    <div class="live-alert-strip">
        <div class="marquee-content" id="live-marquee">
            <div class="marquee-item">LIVE UPDATES: Fresh prepared meals rescued across Kathmandu Valley</div>
            <div class="marquee-item">Rs. <?php echo number_format($total_money_raised); ?> raised for cold storage transport & fuel</div>
            <div class="marquee-item">Active volunteer logistics dispatching warm food to local shelters</div>
            <div class="marquee-item">LIVE UPDATES: Fresh prepared meals rescued across Kathmandu Valley</div>
            <div class="marquee-item">Rs. <?php echo number_format($total_money_raised); ?> raised for cold storage transport & fuel</div>
        </div>
    </div>

    <!-- Navigation -->
    <?php require_once 'includes/navbar.php'; ?>

    <!-- Clean Hero Section -->
    <section class="hero-section container" style="margin-top: 40px; padding-bottom: 50px;">
        <div class="hero-content" style="max-width: 820px; margin: 0 auto; text-align: center;">
            
            <h1 class="hero-title" style="font-size: 4.2rem; margin-bottom: 24px; line-height: 1.15;">
                Share Surplus Food.<br>
                <span class="text-gradient">Bridge Hunger & Hope in Nepal.</span>
            </h1>
            
            <p class="hero-subtitle" style="font-size: 1.25rem; max-width: 720px; margin: 0 auto 36px; color: var(--text-muted); font-weight: 500;">
                A decentralized platform connecting restaurants, party banquets, & generous donors directly with verified shelter homes and rapid volunteer dispatchers across Nepal.
            </p>
            
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="register.php?role=donor" class="btn btn-primary">
                    Donate Food Surplus
                </a>
                <a href="donate_money.php" class="btn btn-gold">
                    Financial Sewa (Rs. <?php echo number_format($total_money_raised); ?>)
                </a>
                <a href="register.php?role=volunteer" class="btn btn-secondary">
                    Join Volunteer Corps
                </a>
                <a href="register.php?role=ngo" class="btn btn-outline">
                    I Need Food (NGO)
                </a>
            </div>

        </div>
    </section>

    <!-- Quick Action Hub (4 Cards) -->
    <section class="container" style="padding: 30px 0 50px;">
        <div class="hub-grid">
            <!-- Hub 1: Donors -->
            <div class="hub-card">
                <div>
                    <div class="hub-icon">👨‍🍳</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 10px; color: var(--text-main);">For Donors & Caterers</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 20px;">
                        Have extra prepared food or party surplus? Post details, location, and pickup time.
                    </p>
                </div>
                <a href="donate_food.php" class="btn btn-primary" style="width: 100%;">Post Food Listing &rarr;</a>
            </div>

            <!-- Hub 2: Financial Sewa -->
            <div class="hub-card">
                <div>
                    <div class="hub-icon" style="color: #ffd700;">💰</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 10px; color: var(--text-main);">Financial Sewa Fund</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 20px;">
                        Support thermal food boxes, vehicle fuel, and emergency food relief packages.
                    </p>
                </div>
                <a href="donate_money.php" class="btn btn-gold" style="width: 100%;">Contribute Sewa &rarr;</a>
            </div>

            <!-- Hub 3: Volunteers -->
            <div class="hub-card">
                <div>
                    <div class="hub-icon" style="color: #38bdf8;">🚴</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 10px; color: var(--text-main);">Logistics Transport</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 20px;">
                        Volunteer drivers receive local alerts to pick up and deliver food safely.
                    </p>
                </div>
                <a href="register.php?role=volunteer" class="btn btn-secondary" style="width: 100%;">Join Transport Corps &rarr;</a>
            </div>

            <!-- Hub 4: NGOs & Communities -->
            <div class="hub-card">
                <div>
                    <div class="hub-icon" style="color: #f43f5e;">🏢</div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 10px; color: var(--text-main);">Shelters & Community NGOs</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 20px;">
                        Get verified to receive instant alerts when fresh food is available nearby.
                    </p>
                </div>
                <a href="register.php?role=ngo" class="btn btn-outline" style="width: 100%;">Register Organization &rarr;</a>
            </div>
        </div>
    </section>

    <!-- Project Image Showcase 1: Volunteers in Nepal (nepali_volunteers.png) -->
    <section class="container" style="padding: 40px 0;">
        <div class="glass-card" style="padding: 36px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
                <div>
                    <span class="badge-pill badge-primary">COMMUNITY LOGISTICS (SEWA)</span>
                    <h2 style="font-size: 2.2rem; margin-top: 12px; margin-bottom: 18px; color: var(--text-main);">
                        Dedicated Volunteers Delivering Hope Across Nepal
                    </h2>
                    <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7; margin-bottom: 24px;">
                        From the bustling streets of Kathmandu and Lalitpur to surrounding communities, our verified volunteer network collects surplus food from events and restaurants to deliver warm, nourishing meals directly to shelter homes and elderly care centers.
                    </p>

                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="register.php?role=volunteer" class="btn btn-primary">Become a Volunteer Driver</a>
                        <a href="directory.php" class="btn btn-outline">View Directory Map</a>
                    </div>
                </div>

                <div style="position: relative;">
                    <img src="assets/Images/nepali_volunteers.png" alt="Nepali Volunteers Food Rescue" style="width:100%; border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 12px 36px rgba(0,0,0,0.4); display: block;">
                </div>
            </div>
        </div>
    </section>

    <!-- Real Available Surplus Food Listings Feed -->
    <section class="container" style="padding: 30px 0 40px;">
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h2 style="font-size: 1.8rem; color: var(--text-main);">Active Surplus Food Listings</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Available food ready for collection and delivery</p>
                </div>
                <a href="directory.php" class="btn btn-outline" style="padding: 8px 20px; font-size: 0.88rem;">Explore All Listings &rarr;</a>
            </div>

            <?php if (!empty($active_food_feed)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;" id="live-feed-container">
                    <?php foreach ($active_food_feed as $feed): ?>
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 14px; padding: 20px;" class="feed-item-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <span class="badge-pill <?php echo $feed['food_type'] === 'veg' ? 'badge-primary' : 'badge-accent'; ?>">
                                    <?php echo strtoupper($feed['food_type']); ?>
                                </span>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">
                                    Exp: <?php echo date('h:i A', strtotime($feed['expiry_datetime'])); ?>
                                </span>
                            </div>

                            <h4 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 8px;">
                                <?php echo htmlspecialchars($feed['title']); ?>
                            </h4>

                            <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 14px;">
                                📍 <strong>Location:</strong> <?php echo htmlspecialchars($feed['pickup_location']); ?><br>
                                📦 <strong>Quantity:</strong> <?php echo htmlspecialchars($feed['quantity']); ?><br>
                                👨‍🍳 <strong>Donor:</strong> <?php echo htmlspecialchars($feed['donor_name']); ?>
                            </p>

                            <a href="directory.php" class="btn btn-primary" style="width: 100%; padding: 8px 16px; font-size: 0.85rem;">
                                Claim / Rescue Food
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 30px 20px; color: var(--text-muted);">
                    <p style="font-size: 1rem;">All recent food listings have been claimed by local shelters and volunteers!</p>
                    <a href="donate_food.php" class="btn btn-primary" style="margin-top: 12px;">Post New Food Listing</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Project Image Showcase 2: Fresh Nepali Food (nepali_food.png) -->
    <section class="container" style="padding: 40px 0;">
        <div class="glass-card" style="padding: 36px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
                <div style="order: 2;">
                    <span class="badge-pill badge-gold">HYGIENIC & NUTRITIOUS RECOVERY</span>
                    <h2 style="font-size: 2.2rem; margin-top: 12px; margin-bottom: 18px; color: var(--text-main);">
                        Fresh Nepali Food Quality Standards (Suddha Khana)
                    </h2>
                    <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7; margin-bottom: 24px;">
                        We ensure strict food safety guidelines. Prepared dishes (Dal Bhat, Momos, banquet surplus) and fresh ingredients are inspected for shelf-life, handled in food-grade thermal containers, and distributed hot and fresh.
                    </p>

                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="donate_food.php" class="btn btn-gold">List Prepared Surplus</a>
                        <a href="about.php" class="btn btn-outline">Read Safety Standards</a>
                    </div>
                </div>

                <div style="order: 1;">
                    <img src="assets/Images/nepali_food.png" alt="Fresh Nepali Food Recovery" style="width:100%; border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 12px 36px rgba(0,0,0,0.4); display: block;">
                </div>
            </div>
        </div>
    </section>

    <!-- Project Image Showcase 3: Community Outreach Hero (contact_hero.jpg) -->
    <section class="container" style="padding: 40px 0;">
        <div class="glass-card" style="padding: 0; overflow: hidden;">
            <div style="position: relative; min-height: 380px; display: flex; align-items: center; padding: 40px;">
                <img src="assets/Images/contact_hero.jpg" alt="Community Food Rescue Nepal" style="position: absolute; top:0; left:0; width:100%; height:100%; object-fit: cover; z-index: 1; filter: brightness(0.45);">
                <div style="position: relative; z-index: 2; max-width: 650px;">
                    <span class="badge-pill badge-primary" style="margin-bottom: 12px;">GRASSROOTS IMPACT</span>
                    <h2 style="font-size: 2.5rem; color: #ffffff; margin-bottom: 16px; font-weight: 800;">
                        Together, We Can Eliminate Food Waste in Nepal
                    </h2>
                    <p style="color: #e2e8f0; font-size: 1.1rem; line-height: 1.6; margin-bottom: 24px;">
                        Every meal saved represents environmental protection and immediate hunger relief. Join hands with hundreds of donors, volunteers, and shelters making a real difference every day.
                    </p>
                    <a href="contact.php" class="btn btn-primary">Get In Touch & Join Us &rarr;</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted Payment Partners Showcase -->
    <section class="container" style="padding: 30px 0 50px;">
        <div class="glass-card" style="text-align: center; padding: 30px;">
            <p style="color: var(--text-muted); font-size: 0.92rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
                Secure & Transparent Financial Sewa Partners
            </p>
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="background:#ffffff; padding: 8px 16px; border-radius: 10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <img src="assets/Images/khalti_logo.jpeg" alt="Khalti Payment" style="height: 32px; object-fit: contain;">
                </div>
                <div style="background:#ffffff; padding: 8px 16px; border-radius: 10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <img src="assets/Images/esewa_logo.png" alt="eSewa Payment" style="height: 32px; object-fit: contain;">
                </div>
                <div style="background:#ffffff; padding: 8px 16px; border-radius: 10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <img src="assets/Images/connectips_logo.jpg" alt="ConnectIPS" style="height: 32px; object-fit: contain;">
                </div>
                <div style="background:#ffffff; padding: 8px 16px; border-radius: 10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <img src="assets/Images/bank_icon_modern.webp" alt="Direct Bank Transfer" style="height: 32px; object-fit: contain;">
                </div>
                <div style="background:#ffffff; padding: 8px 16px; border-radius: 10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <img src="assets/Images/paypal_logo.svg" alt="PayPal International" style="height: 32px; object-fit: contain;">
                </div>
                <div style="background:#ffffff; padding: 8px 16px; border-radius: 10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <img src="assets/Images/payment_card_processed.jpg" alt="Credit / Debit Card" style="height: 32px; object-fit: contain;">
                </div>
            </div>
        </div>
    </section>

    <!-- Clean Wall of Champions (Leaderboard Preview) -->
    <section id="leaderboard" class="container" style="padding: 30px 0 50px;">
        <div class="glass-card">
            <h2 class="text-gradient-gold" style="text-align: center; font-size: 2.2rem; margin-bottom: 8px;">Wall of Fame & Champions</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">
                Honoring generous financial donors, food heroes, and logistics volunteers across Nepal.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                <!-- Financial Column -->
                <div style="background: rgba(255,215,0,0.03); border: 1px solid rgba(255,215,0,0.12); border-radius: 16px; padding: 20px;">
                    <h4 style="color: var(--gold); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;">
                        💰 Top Contributors
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php 
                        $rank = 1;
                        if(empty($home_top_donors)) {
                            echo '<p style="color:var(--text-muted); font-size:0.88rem;">No financial donations recorded yet.</p>';
                        }
                        foreach($home_top_donors as $hd): 
                            $is_champ = $rank === 1;
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: <?php echo $is_champ ? 'rgba(255,215,0,0.1)' : 'rgba(255,255,255,0.02)'; ?>; border-radius: 10px; border: 1px solid <?php echo $is_champ ? 'rgba(255,215,0,0.3)' : 'rgba(255,255,255,0.05)'; ?>;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 700; color: var(--text-muted);">#<?php echo $rank; ?></span>
                                <div>
                                    <div style="font-weight: 700; color: <?php echo $is_champ ? 'var(--gold)' : 'var(--text-main)'; ?>;">
                                        <?php echo htmlspecialchars($hd['donor_name']); ?>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Sewa Benefactor</div>
                                </div>
                            </div>
                            <span style="color: var(--gold); font-weight: 800; font-size: 0.95rem;">Rs. <?php echo number_format($hd['amount']); ?></span>
                        </div>
                        <?php $rank++; endforeach; ?>
                    </div>
                </div>

                <!-- Food Column -->
                <div style="background: rgba(0,255,163,0.03); border: 1px solid rgba(0,255,163,0.12); border-radius: 16px; padding: 20px;">
                    <h4 style="color: var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;">
                        👨‍🍳 Food Rescue Champions
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php 
                        $rank_f = 1;
                        if(empty($home_top_food)) {
                            echo '<p style="color:var(--text-muted); font-size:0.88rem;">No food listings recorded yet.</p>';
                        }
                        foreach($home_top_food as $fd): 
                            $is_champ = $rank_f === 1;
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: <?php echo $is_champ ? 'rgba(0,255,163,0.1)' : 'rgba(255,255,255,0.02)'; ?>; border-radius: 10px; border: 1px solid <?php echo $is_champ ? 'rgba(0,255,163,0.3)' : 'rgba(255,255,255,0.05)'; ?>;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 700; color: var(--text-muted);">#<?php echo $rank_f; ?></span>
                                <div>
                                    <div style="font-weight: 700; color: <?php echo $is_champ ? 'var(--primary)' : 'var(--text-main)'; ?>;">
                                        <?php echo htmlspecialchars($fd['donor_name']); ?>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">ZeroWaste Partner</div>
                                </div>
                            </div>
                            <span style="color: var(--primary); font-weight: 800; font-size: 0.95rem;"><?php echo $fd['donation_count']; ?> Listings</span>
                        </div>
                        <?php $rank_f++; endforeach; ?>
                    </div>
                </div>

                <!-- Volunteer Column -->
                <div style="background: rgba(56,189,248,0.03); border: 1px solid rgba(56,189,248,0.12); border-radius: 16px; padding: 20px;">
                    <h4 style="color: #38bdf8; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;">
                        🚴 Active Logistics Drivers
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php 
                        $rank_v = 1;
                        if(empty($home_top_volunteers)) {
                            echo '<p style="color:var(--text-muted); font-size:0.88rem;">No volunteer deliveries recorded yet.</p>';
                        }
                        foreach($home_top_volunteers as $vd): 
                            $is_champ = $rank_v === 1;
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: <?php echo $is_champ ? 'rgba(56,189,248,0.1)' : 'rgba(255,255,255,0.02)'; ?>; border-radius: 10px; border: 1px solid <?php echo $is_champ ? 'rgba(56,189,248,0.3)' : 'rgba(255,255,255,0.05)'; ?>;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 700; color: var(--text-muted);">#<?php echo $rank_v; ?></span>
                                <div>
                                    <div style="font-weight: 700; color: <?php echo $is_champ ? '#38bdf8' : 'var(--text-main)'; ?>;">
                                        <?php echo htmlspecialchars($vd['volunteer_name']); ?>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Sewa Logistics Champion</div>
                                </div>
                            </div>
                            <span style="color: #38bdf8; font-weight: 800; font-size: 0.95rem;"><?php echo $vd['delivery_count']; ?> Trips</span>
                        </div>
                        <?php $rank_v++; endforeach; ?>
                    </div>
                </div>
            </div>

            <div style="margin-top: 25px; text-align: center;">
                <a href="leaderboard.php" class="btn btn-gold" style="padding: 10px 30px;">
                    View Complete National Leaderboard &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="container" style="padding: 40px 0; border-top: 1px solid var(--glass-border); margin-top: 50px; text-align: center; color: var(--text-muted);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/Images/admin_logo_3d.gif" alt="ZeroWaste Logo" style="height: 36px; border-radius: 6px;">
                <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">ZeroWaste-ZeroHunger Nepal</span>
            </div>

            <div style="display: flex; gap: 20px; font-size: 0.9rem;">
                <a href="about.php" style="color: var(--text-muted); text-decoration: none;">About Us</a>
                <a href="impact.php" style="color: var(--text-muted); text-decoration: none;">Our Impact</a>
                <a href="leaderboard.php" style="color: var(--text-muted); text-decoration: none;">Leaderboard</a>
                <a href="feedback.php" style="color: var(--text-muted); text-decoration: none;">Feedback</a>
            </div>
        </div>

        <p style="font-size: 0.88rem;">&copy; <?php echo date('Y'); ?> ZeroWaste-ZeroHunger. Decentralized Food Rescue Network. Bridging Hunger & Hope.</p>
    </footer>

    <!-- Three.js Module -->
    <script type="module" src="assets/js/hero-3d.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- Offline Service Worker Registration -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js')
            .then(() => console.log('Service Worker registered for ZeroWaste'))
            .catch(err => console.error('SW registration failed', err));
    }
    </script>

    <!-- Live Feed Auto-Refresh (every 30 seconds) -->
    <script>
    (function() {
        var feedContainer = document.getElementById('live-feed-container');
        var marquee = document.getElementById('live-marquee');
        var liveIndicator = document.querySelector('.live-alert-strip');

        function renderFeedCard(item) {
            var badge = item.food_type === 'veg' ? 'badge-primary' : 'badge-accent';
            return '<div style="background:rgba(255,255,255,0.03);border:1px solid var(--glass-border);border-radius:14px;padding:20px;" class="feed-item-card">' +
                '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">' +
                    '<span class="badge-pill ' + badge + '">' + item.food_type.toUpperCase() + '</span>' +
                    '<span style="font-size:0.78rem;color:var(--text-muted);">Exp: ' + item.expiry_time + '</span>' +
                '</div>' +
                '<h4 style="font-size:1.15rem;color:var(--text-main);margin-bottom:8px;">' + item.title + '</h4>' +
                '<p style="font-size:0.88rem;color:var(--text-muted);margin-bottom:14px;">' +
                    '\u{1F4CD} <strong>Location:</strong> ' + item.pickup_location + '<br>' +
                    '\u{1F4E6} <strong>Quantity:</strong> ' + item.quantity + '<br>' +
                    '\ud83d\udc68\u200d\ud83c\udf73 <strong>Donor:</strong> ' + item.donor_name +
                '</p>' +
                '<a href="directory.php" class="btn btn-primary" style="width:100%;padding:8px 16px;font-size:0.85rem;">Claim / Rescue Food</a>' +
                '</div>';
        }

        function refreshFeed() {
            fetch('api/live_feed.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) return;

                    // Update feed container
                    if (feedContainer) {
                        if (data.feed.length > 0) {
                            feedContainer.innerHTML = data.feed.map(renderFeedCard).join('');
                        } else {
                            feedContainer.innerHTML = '<div style="text-align:center;padding:30px 20px;color:var(--text-muted);grid-column:1/-1;">' +
                                '<p>All recent food listings have been claimed!</p>' +
                                '<a href="donate_food.php" class="btn btn-primary" style="margin-top:12px;">Post New Food Listing</a></div>';
                        }
                    }

                    // Update marquee money figure
                    if (marquee) {
                        var items = marquee.querySelectorAll('.marquee-item');
                        items.forEach(function(el) {
                            if (el.textContent.indexOf('raised') !== -1) {
                                el.textContent = 'Rs. ' + data.total_money + ' raised for cold storage transport & fuel';
                            }
                        });
                    }

                    // Flash indicator to show live update happened
                    if (liveIndicator) {
                        liveIndicator.style.opacity = '0.5';
                        setTimeout(function() { liveIndicator.style.opacity = '1'; }, 300);
                    }

                    console.log('[ZeroWaste] Live feed refreshed at ' + data.timestamp);
                })
                .catch(function(err) {
                    console.warn('[ZeroWaste] Live feed update failed:', err);
                });
        }

        // Initial refresh after 5s, then every 30s
        setTimeout(refreshFeed, 5000);
        setInterval(refreshFeed, 30000);
    })();
    </script>
</body>
</html>
