<?php
session_start();
require_once '../src/functions.php';
$flash = getFlash();
$role_param = isset($_GET['role']) ? $_GET['role'] : 'donor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at bottom left, var(--bg-panel), var(--bg-body));
        }
        
        /* Shared Form Styles */
        .input-group { position: relative; margin-bottom: 20px; }
        .form-input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: var(--bg-input);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            transition: color 0.3s;
        }
        .form-input:focus + .input-icon,
        .form-input:not(:placeholder-shown) + .input-icon {
            color: var(--primary);
        }
        
        /* Floating Labels */
        .floating-label {
            position: absolute; 
            left: 45px; 
            top: 15px; 
            pointer-events: none; 
            color: var(--text-muted); 
            transition: all 0.3s; 
            opacity: 0.7;
        }
        .form-input:focus ~ .floating-label,
        .form-input:not(:placeholder-shown) ~ .floating-label {
            top: -10px;
            left: 0;
            font-size: 0.8rem;
            color: var(--primary);
        }

        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
            display: block;
            text-decoration: none;
        }

        .corner-toggle { position: absolute; top: 20px; right: 20px; }
    </style>
</head>
<body>

    <div class="corner-toggle">
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode"></button>
    </div>
    
    <link rel="stylesheet" href="assets/css/3d_logo.css">
    <div class="glass-card auth-card" style="width: 100%; max-width: 480px; animation: fadeInUp 0.5s ease;">
        <div class="auth-header logo-3d-container">
            <a href="index.php" style="display:inline-block; text-decoration:none;">
                <img src="assets/Images/admin_logo_3d.gif" alt="ZeroWaste-ZeroHunger" class="logo-3d admin-logo-3d" style="width: 90px; height: 90px; margin-bottom: 15px;">
            </a>
            <h2 style="font-size: 1.5rem; color: var(--text-main);">Join the Movement</h2>
            <p style="color: var(--text-muted); margin-top: 5px;">Create an account to start sharing or saving food.</p>
        </div>
        
        <?php if($flash): ?>
            <div style="background: <?php echo $flash['type'] == 'error' ? 'rgba(255,0,80,0.1)' : 'rgba(0,255,136,0.1)'; ?>; padding: 15px; border-radius: 8px; margin-bottom: 25px; color: var(--text-main); border: 1px solid <?php echo $flash['type'] == 'error' ? '#ff0055' : 'var(--primary)'; ?>; display: flex; align-items: center; gap: 10px;">
                <span><?php echo $flash['type'] == 'error' ? '⚠️' : '✅'; ?></span>
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <form action="../src/auth.php" method="POST">
            <?php echo csrfInput(); ?>

            <div class="input-group">
                <input type="text" name="username" class="form-input" placeholder=" " required>
                <span class="input-icon">👤</span>
                <label class="floating-label">Username</label>
            </div>

            <div class="input-group">
                <input type="email" name="email" class="form-input" placeholder=" " required>
                <span class="input-icon">✉️</span>
                <label class="floating-label">Email Address</label>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" class="form-input" placeholder=" " required>
                <span class="input-icon">🔒</span>
                <label class="floating-label">Password</label>
            </div>

            <div class="input-group">
                <style>
                    select option { background: var(--bg-panel); color: var(--text-main); }
                </style>
                <select name="role" class="form-input" style="cursor: pointer;">
                    <option value="donor" <?php echo $role_param == 'donor' ? 'selected' : ''; ?>>Food Donor (Restaurant/Individual)</option>
                    <option value="ngo" <?php echo $role_param == 'ngo' ? 'selected' : ''; ?>>NGO / Charity</option>
                    <option value="volunteer" <?php echo $role_param == 'volunteer' ? 'selected' : ''; ?>>Volunteer (Transport Partner)</option>
                </select>
                <span class="input-icon">🏷️</span>
                <label class="floating-label" style="top: -10px; left: 0; font-size: 0.8rem; color: var(--primary);">I am a...</label>
            </div>

            <button type="submit" name="register_btn" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 10px;">
                Create Account
            </button>
        </form>

        <p style="text-align: center; margin-top: 25px; color: var(--text-muted); font-size: 0.9rem;">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Sign In</a>
        </p>
    </div>

    <!-- Theme Script -->
    <script src="assets/js/theme.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
