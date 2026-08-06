<?php
// public/admin/donations.php
require_once '../../config/db.php';
$page_title = 'Manage Donations';
require_once 'layout.php';

// Handle Deletion
if (isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM food_listings WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['message' => 'Listing removed permanently.', 'type' => 'success'];
    echo "<script>window.location.href='donations.php';</script>";
    exit;
}

// Fetch Listings
$sql = "
    SELECT f.*, u.username as donor_name, u.email 
    FROM food_listings f 
    JOIN users u ON f.donor_id = u.id 
    ORDER BY f.created_at DESC
";
$listings = $pdo->query($sql)->fetchAll();
?>

<div class="card admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3>All Food Listings</h3>
        <span class="badge" style="background:var(--bg-input); color:var(--text-muted); border:1px solid var(--glass-border);"><?php echo count($listings); ?> Total Posts</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="color:var(--text-muted);">Food Item</th>
                    <th style="color:var(--text-muted);">Donor</th>
                    <th style="color:var(--text-muted);">Status</th>
                    <th style="color:var(--text-muted);">Posted On</th>
                    <th style="color:var(--text-muted); text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($listings as $item): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 15px 0;">
                        <div style="font-weight:bold; color:var(--text-main); display:flex; align-items:center; gap:10px;">
                            <div style="width:32px; height:32px; background:var(--bg-input); border-radius:6px; display:flex; align-items:center; justify-content:center;">🍲</div>
                            <?php echo htmlspecialchars($item['title']); ?>
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-left:42px;">
                            <?php echo htmlspecialchars($item['quantity']); ?> • <?php echo ucfirst($item['food_type']); ?>
                        </div>
                    </td>
                    <td>
                        <div style="color:var(--text-main);"><?php echo htmlspecialchars($item['donor_name']); ?></div>
                        <div style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($item['email']); ?></div>
                    </td>
                    <td>
                        <span class="badge" style="background: <?php echo $item['status'] == 'available' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(245, 158, 11, 0.2)'; ?>; color: <?php echo $item['status'] == 'available' ? '#10b981' : '#f59e0b'; ?>;">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </td>
                    <td style="color:var(--text-muted);"><?php echo date('M d', strtotime($item['created_at'])); ?></td>
                    <td style="text-align:right;">
                        <form method="POST" onsubmit="return confirm('Delete this listing?');">
                            <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-action" style="background:transparent; color:var(--danger); border:1px solid rgba(239, 68, 68, 0.3); width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</body>
</html>
