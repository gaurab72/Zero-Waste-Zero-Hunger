<?php
// public/admin/requests.php
require_once '../../config/db.php';
$page_title = 'Requests';
require_once 'layout.php';

// Handle Actions
if (isset($_POST['action'])) {
    $req_id = $_POST['request_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    
    // Update claim status
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $req_id]);
    
    // If approved, notify NGO (mock notification) and potentially unlock location details
    $_SESSION['flash'] = ['message' => "Request marked as " . strtoupper($new_status), 'type' => 'success'];
    echo "<script>window.location.href='requests.php';</script>";
    exit;
}

// Fetch Pending Requests
$sql = "
    SELECT c.id as claim_id, c.status, c.created_at,
           f.title as food_title, f.quantity,
           u.username as ngo_name, u.email as ngo_email
    FROM claims c
    JOIN food_listings f ON c.listing_id = f.id
    JOIN users u ON c.ngo_id = u.id
    WHERE c.status = 'pending'
    ORDER BY c.created_at ASC
";
$requests = $pdo->query($sql)->fetchAll();
?>

<div class="card admin-card">
    <h3 style="margin-bottom:20px;">🔔 Pending Donation Claims</h3>
    
    <?php if(empty($requests)): ?>
        <p style="text-align:center; color:var(--text-muted); padding:30px;">
            No pending requests at the moment. Good job!
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="color:var(--text-muted);">NGO Name</th>
                        <th style="color:var(--text-muted);">Requested Food</th>
                        <th style="color:var(--text-muted);">Quantity</th>
                        <th style="color:var(--text-muted);">Time</th>
                        <th style="color:var(--text-muted); text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($requests as $r): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 15px 0;">
                            <div style="font-weight:bold; color:var(--text-main);"><?php echo htmlspecialchars($r['ngo_name']); ?></div>
                            <div style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($r['ngo_email']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($r['food_title']); ?></td>
                        <td><?php echo htmlspecialchars($r['quantity']); ?></td>
                        <td style="color:var(--text-muted);"><?php echo date('M d, H:i', strtotime($r['created_at'])); ?></td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex; gap:10px;">
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?php echo $r['claim_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-action" style="background:rgba(16, 185, 129, 0.2); color:#10b981; border:none; padding:5px 15px; border-radius:5px; cursor:pointer;" title="Approve">Approve</button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?php echo $r['claim_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-action" style="background:rgba(239, 68, 68, 0.2); color:#ef4444; border:none; padding:5px 15px; border-radius:5px; cursor:pointer;" title="Reject">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</div>
</body>
</html>
