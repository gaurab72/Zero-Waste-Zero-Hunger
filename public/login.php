<?php
session_start();
require_once '../src/functions.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at top right, var(--bg-panel), var(--bg-body));
        }
        
        /* Floating Labels */
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        .form-input {
            width: 100%;
            padding: 15px 15px 15px 45px; /* Space for icon */
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
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-logo {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
        }
        
        /* Social Login Placeholder */
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .social-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--glass-border);
            background: var(--bg-input);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: var(--text-main);
        }
        .social-btn:hover {
            background: rgba(255,255,255,0.05);
            border-color: var(--text-muted);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--glass-border);
        }
        .divider span {
            padding: 0 10px;
        }

        .corner-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>

    <div class="corner-toggle">
        <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode"></button>
    </div>
    
    <link rel="stylesheet" href="assets/css/3d_logo.css">
    <div class="glass-card auth-card" style="width: 100%; max-width: 420px; animation: fadeInUp 0.5s ease;">
        <div class="auth-header logo-3d-container">
            <a href="index.php" style="display:inline-block; text-decoration:none;">
                <img src="assets/Images/admin_logo_3d.gif" alt="ZeroWaste-ZeroHunger" class="logo-3d admin-logo-3d" style="width: 100px; height: 100px; margin-bottom: 20px;">
            </a>
            <h2 style="font-size: 1.5rem; color: var(--text-main);">Welcome Back</h2>
            <p style="color: var(--text-muted); margin-top: 5px;">Enter your credentials to access your account</p>
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
                <input type="email" name="email" class="form-input" placeholder=" " required>
                <span class="input-icon">✉️</span>
                <label style="position:absolute; left: 45px; top: 15px; pointer-events: none; color: var(--text-muted); transition: all 0.3s; opacity: 0.7;">Email Address</label>
            </div>
            <style>
                .form-input:focus ~ label,
                .form-input:not(:placeholder-shown) ~ label {
                    top: -10px;
                    left: 0;
                    font-size: 0.8rem;
                    color: var(--primary);
                }
            </style>
            
            <div class="input-group">
                <input type="password" name="password" class="form-input" placeholder=" " required>
                <span class="input-icon">🔒</span>
                <label style="position:absolute; left: 45px; top: 15px; pointer-events: none; color: var(--text-muted); transition: all 0.3s; opacity: 0.7;">Password</label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; font-size: 0.9rem;">
                <label style="display: flex; align-items: center; cursor: pointer; color: var(--text-muted);">
                    <input type="checkbox" style="margin-right: 8px; accent-color: var(--primary);"> Remember me
                </label>
                <a href="#" style="color: var(--primary); text-decoration: none;">Forgot Password?</a>
            </div>

            <button type="submit" name="login_btn" class="btn btn-primary" style="width: 100%; padding: 14px;">
                Sign In
            </button>
        </form>

        <div class="divider">
            <span>OR CONTINUE WITH</span>
        </div>

        <div class="social-login" style="flex-direction: column; gap: 10px;">
            <!-- Official Google Sign-In Button -->
            <div id="g_id_onload"
                 data-client_id="YOUR_GOOGLE_CLIENT_ID"
                 data-context="signin"
                 data-ux_mode="popup"
                 data-callback="handleCredentialResponse"
                 data-auto_prompt="false">
            </div>
            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="rectangular"
                 data-theme="outline"
                 data-text="continue_with"
                 data-size="large"
                 data-logo_alignment="left"
                 style="width: 100%;">
            </div>

            <div style="display: flex; gap: 15px; width: 100%;">
                <a href="../src/social_auth.php?provider=facebook" class="social-btn" title="Sign in with Facebook" style="flex: 1;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b8/2021_Facebook_icon.svg" alt="Facebook" style="width:20px;">
                </a>
                <a href="../src/social_auth.php?provider=linkedin" class="social-btn" title="Sign in with LinkedIn" style="flex: 1;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" alt="LinkedIn" style="width:20px;">
                </a>
            </div>
        </div>

        <p style="text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 0.9rem;">
            Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Create Account</a>
        </p>
    </div>

    <!-- Theme Script -->
    <!-- Google Identity Services SDK -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="assets/js/theme.js"></script>
    <script>
        function handleCredentialResponse(response) {
            // Redirect to our social handler with the credential
            window.location.href = `../src/social_auth.php?provider=google&credential=${response.credential}`;
        }
    </script>
    <!-- Animation Keyframes -->
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
