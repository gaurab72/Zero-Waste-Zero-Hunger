<?php
session_start();
require_once '../src/functions.php';
$flash = getFlash();

if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    redirect('admin/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
        }
        body {
            /* Professional Dark Tech Background */
            background: #0f172a; /* Slate 900 */
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.1) 0px, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            color: #fff;
            margin: 0;
        }

        .admin-login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .admin-card {
            background: rgba(30, 41, 59, 0.7); /* Slate 800 with opacity */
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .brand-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            filter: drop-shadow(0 0 15px rgba(56, 189, 248, 0.5));
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        
        .brand-logo:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .brand-subtitle {
            color: #94a3b8;
            font-size: 0.9rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-weight: 500;
            opacity: 0.8;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            color: #cbd5e1; /* Slate 300 */
            font-size: 0.875rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.6); /* Darker inner bg */
            border: 1px solid var(--border);
            border-radius: 8px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #38bdf8; /* Sky 400 */
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
            background: rgba(15, 23, 42, 0.8);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.1s, box-shadow 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 25px;
            color: #64748b;
            font-size: 0.75rem;
        }
    </style>
    <link rel="stylesheet" href="assets/css/3d_logo.css"> 
</head>
<body>

    <div class="admin-login-wrapper">
        <div class="admin-card">
            
            <div class="brand-header logo-3d-container">
                <!-- Company Logo (3D Dynamic) -->
                <img src="assets/Images/admin_logo_3d.gif" alt="Company Logo" class="brand-logo admin-logo-3d">
                <div class="brand-title">Admin Console</div>
                <div class="brand-subtitle">Secure Access Management System</div>
            </div>

            <?php if($flash): ?>
                <div class="alert-box">
                    <span>⚠️</span>
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <form action="../src/auth.php" method="POST">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="login_type" value="admin">
                
                <div class="input-group">
                    <label for="email">Administrator ID</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@company.com" required autocomplete="email">
                </div>
                
                <div class="input-group">
                    <label for="password">Security Key</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
                </div>

                <button type="submit" name="login_btn" class="btn-submit">
                    Authenticate
                </button>
            </form>

            <div class="secure-badge">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                256-bit SSL Encrypted Connection
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #64748b; font-size: 0.875rem; text-decoration: none; transition: color 0.2s;">&larr; Return to Website</a>
        </div>
    </div>

</body>
</html>
