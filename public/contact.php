<?php
// Dependencies and session are handled by navbar.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="margin-top: 50px; padding-bottom: 50px;">
        <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
            <h1 class="text-gradient" style="text-align: center; margin-bottom: 30px;">Get in Touch</h1>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 40px;">
                Have questions about food donation or volunteering? Reach out to our team directly.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Contact Info -->
                <div>
                    <h3 style="color: var(--primary); margin-bottom: 20px;">Contact Information</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--text-muted);">Email Address</span>
                        <a href="mailto:gaurabhamal23@gmail.com" style="color: white; font-size: 1.1rem; text-decoration: none;">gaurabhamal23@gmail.com</a>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--text-muted);">Phone Number</span>
                        <a href="tel:9815114901" style="color: white; font-size: 1.1rem; text-decoration: none;">9815114901</a>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--text-muted);">Support Hotline (Toll Free)</span>
                        <span style="color: white; font-size: 1.1rem;">1-800-FOOD-SAVE</span>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <span style="display: block; font-size: 0.9rem; color: var(--text-muted);">Office Address</span>
                        <span style="color: white; font-size: 1.1rem;">
                            Pokhara-17 Chhorepatan
                        </span>
                    </div>
                </div>

                <!-- Map / Image Area -->
                <div style="background: rgba(255,255,255,0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; min-height: 250px;">
                   <img src="assets/Images/contact_hero.jpg" alt="Contact Support" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px; opacity: 0.8;">
                </div>
            </div>
            
            <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 40px 0;">

            <!-- Simple Form -->
            <h3 style="margin-bottom: 20px;">Send a Message</h3>
            <form>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" placeholder="john@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-input" rows="4" placeholder="How can we help?"></textarea>
                </div>
                <button type="button" class="btn btn-primary">Send Message</button>
            </form>

        </div>
    </div>

</body>
</html>
