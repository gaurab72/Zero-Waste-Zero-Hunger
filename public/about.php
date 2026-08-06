<?php
// Dependencies are handled by navbar.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .about-section {
            padding: 60px 0;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        .team-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .team-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--glass-border);
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--secondary);
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>
    <!-- RE-INSERTING NAV CORRECTLY -->
    <!-- Navbar is included at the top via requires -->

    <div class="container about-section">
        <div class="glass-card" style="margin-bottom: 50px;">
            <h1 class="text-gradient" style="text-align: center; margin-bottom: 30px;">About Our Organization</h1>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-muted); text-align: justify;">
                The <strong>Food Wastage Management System</strong> is a platform dedicated to a specific, heartfelt mission: <strong>Turning surplus party food into smiles for those who need it most.</strong> 
                Weddings, birthdays, and large celebrations often end with massive amounts of delicious, untouched food being thrown away. Meanwhile, there are children in orphanages and elderly people in shelters who often go to sleep hungry.
            </p>
            <br>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-muted); text-align: justify;">
                We bridge this gap. Our system connects event organizers and kind-hearted hosts directly with orphanages ("Bal Mandirs") and elderly care homes. We ensure that the joy of your celebration extends to children who have lost their parents and individuals who have no one else to care for them.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 80px;">
            <div>
                <h2 style="color: var(--secondary); margin-bottom: 20px;">Our Vision</h2>
                <p style="line-height: 1.6; color: var(--text-muted);">
                    To create a compassionate network where every wedding and birthday party contributes to the well-being of the most vulnerable in our society. We envision a world where no child feels alone or hungry, and every celebration shares its abundance.
                </p>
            </div>
            <div>
                <h2 style="color: var(--primary); margin-bottom: 20px;">What We Rescue</h2>
                <ul style="list-style: none; color: var(--text-muted); line-height: 2;">
                    <li>🎉 <strong>Wedding Feasts:</strong> High-quality surplus from marriage receptions.</li>
                    <li>🎂 <strong>Birthday Treats:</strong> Cakes and meals from parties.</li>
                    <li>🤝 <strong>Corporate Events:</strong> Excess food from large gatherings.</li>
                    <li>🏠 <strong>Orphanage Support:</strong> Prioritizing homes for children without parents.</li>
                </ul>
            </div>
        </div>

        <h2 style="text-align: center;">Meet The Founder & Developer</h2>
        <div class="team-grid" style="grid-template-columns: 1fr; max-width: 500px; margin: 50px auto 0;">
            <div class="team-card" style="padding: 40px; border: 2px solid var(--primary); box-shadow: 0 0 30px var(--primary-glow); border-radius: 30px; background: rgba(0, 255, 163, 0.02);">
                <div class="team-avatar-container" style="position: relative; width: 150px; height: 150px; margin: 0 auto 25px;">
                    <img src="assets/Images/admin_profile.png" alt="Gaurab Hamal" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 3px solid var(--primary); box-shadow: 0 0 20px var(--primary-glow);">
                    <div style="position: absolute; bottom: 5px; right: 5px; background: var(--primary); width: 25px; height: 25px; border-radius: 50%; border: 3px solid var(--bg-panel); display: flex; align-items: center; justify-content: center; font-size: 0.7rem;" title="Verified Developer">✔️</div>
                </div>
                <h3 style="font-size: 1.8rem; margin-bottom: 5px; letter-spacing: 1px;">Gaurab Hamal</h3>
                <p style="color:var(--primary); font-size: 1.1rem; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">Founder & Lead Developer</p>
                <p style="color:var(--text-muted); font-size: 1rem; margin-top: 20px; line-height: 1.6;">
                    "Driven by the vision of using technology to connect surplus resources with those who need them most. 
                    Building systems that combine modern tech with social impact."
                </p>
                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 20px;">
                    <span style="font-size: 1.5rem; cursor: pointer;" title="GitHub">💻</span>
                    <span style="font-size: 1.5rem; cursor: pointer;" title="LinkedIn">🌐</span>
                    <span style="font-size: 1.5rem; cursor: pointer;" title="Email">📧</span>
                </div>
            </div>
        </div>

        <div class="glass-card" style="margin-top: 50px;">
            <h2 style="color: var(--primary); text-align: center; margin-bottom: 30px;">Professional Impact & Experience</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-bottom: 40px;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 15px;">🌐</div>
                    <h4>Unified Network</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted);">Real-time coordination between Donors, Volunteers, and NGOs across Nepal.</p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 15px;">💬</div>
                    <h4>Direct Coordination</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted);">A comprehensive directory for volunteers to proactively message and coordinate rescue missions.</p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 15px;">📍</div>
                    <h4>Live Tracking</h4>
                    <p style="font-size: 0.9rem; color: var(--text-muted);">Secure live location sharing for safe and efficient food transport and delivery.</p>
                </div>
            </div>
            <div style="text-align: center; border-top: 1px solid var(--glass-border); padding-top: 30px;">
                <a href="professional_experience.php" class="btn btn-primary" style="padding: 12px 40px;">
                    Explore Technical Showcase & Architecture →
                </a>
            </div>
        </div>

    </div>

    <footer class="container" style="padding: 40px 0; border-top: 1px solid var(--glass-border); margin-top: 50px; text-align: center; color: var(--text-muted);">
        <p>&copy; <?php echo date('Y'); ?> ZeroWaste System. Built with ❤️ for a sustainable future.</p>
    </footer>

</body>
</html>
