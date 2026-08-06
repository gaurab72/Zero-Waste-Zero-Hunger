<?php
// public/includes/footer.php
?>
<footer style="background: var(--bg-panel); border-top: 1px solid var(--glass-border); padding: 40px 0; margin-top: 50px;">
    <div class="container" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 30px;">
        <div style="flex: 1; min-width: 250px;">
            <div class="logo-3d-container" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <img src="assets/Images/admin_logo_3d.gif" alt="Logo" class="logo-3d nav-logo-3d" style="height: 40px; border-radius: 5px;">
                <span style="font-weight: 700; font-size: 1.2rem;">ZeroWaste-<span style="color:var(--primary)">ZeroHunger</span></span>
            </div>
            <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">
                Connecting potential food donors with charitable organizations to reduce food wastage and eliminate hunger in our communities.
            </p>
        </div>
        
        <div style="flex: 1; min-width: 200px;">
            <h4 style="margin-bottom: 20px;">Quick Links</h4>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="index.php" style="color: var(--text-muted); text-decoration: none;">Home</a>
                <a href="about.php" style="color: var(--text-muted); text-decoration: none;">About Us</a>
                <a href="impact.php" style="color: var(--text-muted); text-decoration: none;">Our Impact</a>
                <a href="contact.php" style="color: var(--text-muted); text-decoration: none;">Contact</a>
            </div>
        </div>
        
        <div style="flex: 1; min-width: 250px;">
            <h4 style="margin-bottom: 20px;">Contact Us</h4>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 10px;">
                📍 Pokhara 17, Chhorapatan
            </p>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 10px;">
                📧 gaurabhamal23@gmail.com
            </p>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                📞 9815114901
            </p>
        </div>
    </div>
    <div style="text-align: center; border-top: 1px solid var(--glass-border); margin-top: 30px; padding-top: 20px; color: var(--text-muted); font-size: 0.85rem;">
        &copy; <?php echo date('Y'); ?> ZeroWaste-ZeroHunger. Built with ❤️ by <strong>Gaurab Hamal</strong>.
    </div>
</footer>
