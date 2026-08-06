<?php
// public/admin/users.php
require_once '../../config/db.php';
$page_title = 'User Management';
require_once 'layout.php';

// HANDLE ACTIONS (CRUD)
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'];
    
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        $_SESSION['flash'] = ['message' => 'User deleted successfully', 'type' => 'success'];
    } elseif ($action === 'approve_kyc') {
        $pdo->prepare("UPDATE users SET kyc_status = 'approved' WHERE id = ?")->execute([$user_id]);
        $_SESSION['flash'] = ['message' => 'NGO Approved & Verified ✅', 'type' => 'success'];
    } elseif ($action === 'reject_kyc') {
        $pdo->prepare("UPDATE users SET kyc_status = 'rejected' WHERE id = ?")->execute([$user_id]);
        $_SESSION['flash'] = ['message' => 'NGO Rejected ❌', 'type' => 'error'];
    }
    
    echo "<script>window.location.href='users.php';</script>";
    exit;
}

// Fetch Users
$sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
$users = $pdo->query($sql)->fetchAll();
?>

<div class="card admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3>All Registered Users</h3>
        <button onclick="window.print()" class="btn-action" style="background: var(--bg-panel); border:1px solid var(--glass-border); color:var(--text-main); cursor:pointer;">🖨️ Print Report</button>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="color:var(--text-muted);">User</th>
                    <th style="color:var(--text-muted);">Role</th>
                    <th style="color:var(--text-muted);">Email</th>
                    <th style="color:var(--text-muted);">Status / KYC</th>
                    <th style="color:var(--text-muted); text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 15px 0;">
                        <div style="font-weight:600; color:var(--text-main);"><?php echo htmlspecialchars($user['username']); ?></div>
                        <div style="font-size:0.8rem; color:var(--text-muted);">ID: #<?php echo $user['id']; ?></div>
                    </td>
                    <td>
                        <span class="badge" style="background: <?php echo $user['role'] === 'ngo' ? 'rgba(245, 158, 11, 0.2)' : 'rgba(16, 185, 129, 0.2)'; ?>; color: <?php echo $user['role'] === 'ngo' ? '#f59e0b' : '#10b981'; ?>;">
                            <?php echo strtoupper($user['role']); ?>
                        </span>
                    </td>
                    <td style="color:var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php if($user['role'] === 'ngo'): ?>
                            <?php if($user['kyc_status'] === 'pending'): ?>
                                <span class="badge" style="background:rgba(239, 68, 68, 0.2); color:#ef4444;">PENDING UPLOAD</span>
                            <?php elseif($user['kyc_status'] === 'submitted'): ?>
                                <span class="badge" style="background:rgba(245, 158, 11, 0.2); color:#f59e0b;">NEEDS REVIEW</span>
                                <?php if(!empty($user['kyc_file'])): ?>
                                    <div style="margin-top:5px;">
                                        <a href="../<?php echo htmlspecialchars($user['kyc_file']); ?>" target="_blank" style="color:var(--primary); font-size:0.8rem; text-decoration:underline;">View Document</a>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($user['kyc_status'] === 'rejected'): ?>
                                <span class="badge" style="background:rgba(239, 68, 68, 0.2); color:#ef4444;">REJECTED</span>
                            <?php else: ?>
                                <span class="badge" style="background:rgba(16, 185, 129, 0.2); color:#10b981;">VERIFIED</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:10px;">
                            <?php if($user['kyc_status'] === 'pending' || $user['kyc_status'] === 'submitted'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="approve_kyc">
                                    <button type="submit" class="btn-action" style="background:rgba(16, 185, 129, 0.2); color:#10b981; border:none; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Approve">✓</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="reject_kyc">
                                    <button type="submit" class="btn-action" style="background:rgba(239, 68, 68, 0.2); color:#ef4444; border:none; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Reject">✕</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-action" style="background:transparent; color:var(--text-muted); border:1px solid var(--glass-border); width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- End Main Content -->
</body>
</html>
