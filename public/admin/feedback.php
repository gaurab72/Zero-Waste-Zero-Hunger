<?php
// public/admin/feedback.php
$page_title = 'App Feedback';
require_once 'layout.php'; // Include layout header/sidebar

// Fetch feedbacks
$stmt = $pdo->prepare("
    SELECT f.*, u.username as registered_username 
    FROM feedbacks f 
    LEFT JOIN users u ON f.user_id = u.id 
    ORDER BY f.created_at DESC
");
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Area (layout.php opens main-content) -->
<div class="container-fluid" style="padding: 20px;">

    <div class="card" style="background: var(--bg-panel); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px;">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0;">User Feedback (<?php echo count($feedbacks); ?>)</h4>
            <!-- Optional: CSV Export button could go here -->
        </div>

        <div class="table-container" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border); text-align: left;">
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Date</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">User / Email</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Rating</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Subject</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Message</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                No feedback received yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <td style="padding: 12px; font-size: 0.9rem;">
                                    <?php echo date('M d, Y', strtotime($fb['created_at'])); ?><br>
                                    <small style="color: var(--text-muted);"><?php echo date('H:i', strtotime($fb['created_at'])); ?></small>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="font-weight: 500; font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($fb['name']); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($fb['email']); ?>
                                        <?php if($fb['registered_username']): ?>
                                            <span style="color: var(--primary);">(@<?php echo htmlspecialchars($fb['registered_username']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="color: #fbbf24; font-size: 1rem;">
                                        <?php echo str_repeat('★', $fb['rating']); ?><span style="color: #cbd5e1;"><?php echo str_repeat('★', 5 - $fb['rating']); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 12px; font-size: 0.95rem; font-weight: 500;">
                                    <?php echo htmlspecialchars($fb['subject'] ?: 'No Subject'); ?>
                                </td>
                                <td style="padding: 12px; font-size: 0.9rem; max-width: 300px;">
                                    <div style="background: rgba(0,0,0,0.03); padding: 8px; border-radius: 6px;">
                                        <?php echo nl2br(htmlspecialchars($fb['message'])); ?>
                                    </div>
                                </td>
                                <td style="padding: 12px;">
                                    <button class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 4px 10px;" onclick="alert('Reply feature coming soon!')">Reply</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Optional: Add simple JS for interacting with rows if needed
</script>

</body>
</html>
