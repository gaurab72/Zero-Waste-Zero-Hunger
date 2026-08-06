<?php
// public/admin/profile.php
require_once '../../config/db.php';
$page_title = 'Admin Profile';
require_once 'layout.php';

$user = getCurrentUser($pdo);
$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Profile Info
    if (isset($_POST['update_profile'])) {
        $username = sanitize($_POST['username']);
        
        if (!empty($username)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            if ($stmt->execute([$username, $_SESSION['user_id']])) {
                $_SESSION['username'] = $username;
                $success = "Profile updated successfully!";
                $user = getCurrentUser($pdo);
            } else {
                $error = "Failed to update profile.";
            }
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (password_verify($current_pass, $user['password_hash'])) {
            if ($new_pass === $confirm_pass) {
                if (strlen($new_pass) >= 6) {
                    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    if ($stmt->execute([$new_hash, $_SESSION['user_id']])) {
                        $success = "Password changed successfully!";
                    } else {
                        $error = "Failed to update password.";
                    }
                } else {
                    $error = "New password must be at least 6 characters.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Incorrect current password.";
        }
    }
}
?>

<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">
    
    <!-- ADMIN IDENTITY HERO CARD -->
    <div class="admin-card" style="background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(12,19,16,0.9)); border-color: rgba(16,185,129,0.3); display: flex; align-items: center; justify-content: space-between; padding: 28px 32px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="position: relative;">
                <img src="../assets/Images/admin_logo_3d.gif" alt="Admin Avatar" style="width: 72px; height: 72px; border-radius: 16px; border: 2px solid var(--primary-green-bright); object-fit: cover; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);">
                <span style="position: absolute; bottom: -3px; right: -3px; width: 14px; height: 14px; background: #00e676; border: 2.5px solid var(--admin-bg); border-radius: 50%; box-shadow: 0 0 10px #00e676;"></span>
            </div>
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin: 0;"><?php echo htmlspecialchars($admin_name); ?></h2>
                    <span class="badge-pill success">Verified System Admin</span>
                </div>
                <p style="color: var(--primary-green-bright); font-size: 0.92rem; font-weight: 500; margin: 4px 0 0 0;"><?php echo htmlspecialchars($admin_email); ?></p>
            </div>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 0.76rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">Account Status</span>
            <div style="color: #00e676; font-weight: 700; font-size: 0.95rem; margin-top: 4px; display: flex; align-items: center; gap: 6px; justify-content: flex-end;">
                <span style="width: 8px; height: 8px; background: #00e676; border-radius: 50%;"></span> Active Session
            </div>
        </div>
    </div>

    <!-- PROFILE SETTINGS FORM CARD -->
    <div class="admin-card">
        <h3 style="margin-bottom: 25px; border-bottom: 1px solid var(--admin-card-border); padding-bottom: 15px; font-size: 1.1rem; color: var(--text-primary);">Account Settings</h3>

        <?php if($success): ?>
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid var(--primary-green); color: var(--primary-green-bright); padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--accent-danger); color: var(--accent-danger); padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
            <!-- Left Col: Personal Info -->
            <div>
                <form method="POST">
                    <div style="margin-bottom: 18px;">
                        <label style="display:block; margin-bottom:6px; color:var(--text-dim); font-size:0.85rem; font-weight:600;">Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($admin_email); ?>" disabled style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.02); border:1px solid var(--admin-card-border); border-radius:10px; color:var(--text-primary); opacity:0.8; cursor:not-allowed; font-size:0.9rem;">
                        <small style="color:var(--text-dim); font-size:0.75rem; margin-top:4px; display:block;">Primary administrator login email.</small>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display:block; margin-bottom:6px; color:var(--text-dim); font-size:0.85rem; font-weight:600;">System Role</label>
                        <input type="text" value="System Administrator" disabled style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.02); border:1px solid var(--admin-card-border); border-radius:10px; color:var(--text-primary); opacity:0.8; cursor:not-allowed; font-size:0.9rem;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display:block; margin-bottom:6px; color:var(--text-dim); font-size:0.85rem; font-weight:600;">Admin Display Name</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($admin_name); ?>" style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.04); border:1px solid var(--admin-card-border); border-radius:10px; color:var(--text-primary); font-size:0.9rem; outline:none;" required>
                    </div>

                    <button type="submit" name="update_profile" style="background: var(--primary-green); color: #000; font-weight: 700; border: none; padding: 10px 22px; border-radius: 10px; cursor: pointer; margin-top: 10px; transition: all 0.2s ease;">Save Changes</button>
                </form>
            </div>

            <!-- Right Col: Security -->
            <div style="padding-left: 32px; border-left: 1px solid var(--admin-card-border);">
                <h4 style="margin-bottom: 20px; color: var(--text-primary); font-size: 1rem;">Security & Password</h4>
                
                <form method="POST">
                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; color:var(--text-dim); font-size:0.85rem; font-weight:600;">Current Password</label>
                        <input type="password" name="current_password" style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.04); border:1px solid var(--admin-card-border); border-radius:10px; color:var(--text-primary); font-size:0.9rem; outline:none;" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; color:var(--text-dim); font-size:0.85rem; font-weight:600;">New Password</label>
                        <input type="password" name="new_password" style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.04); border:1px solid var(--admin-card-border); border-radius:10px; color:var(--text-primary); font-size:0.9rem; outline:none;" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; color:var(--text-dim); font-size:0.85rem; font-weight:600;">Confirm New Password</label>
                        <input type="password" name="confirm_password" style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.04); border:1px solid var(--admin-card-border); border-radius:10px; color:var(--text-primary); font-size:0.9rem; outline:none;" required>
                    </div>

                    <button type="submit" name="change_password" style="background: rgba(239, 68, 68, 0.2); color: var(--accent-danger); border: 1px solid rgba(239, 68, 68, 0.4); font-weight: 600; padding: 10px 22px; border-radius: 10px; cursor: pointer; margin-top: 10px; transition: all 0.2s ease;">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

</main> <!-- End Main Content -->
</body>
</html>
