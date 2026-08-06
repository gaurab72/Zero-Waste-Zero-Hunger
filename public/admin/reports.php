<?php
// public/admin/reports.php
require_once '../../config/db.php';
$page_title = 'System Reports';
require_once 'layout.php';

// Aggregate Data
$total_food = $pdo->query("SELECT COUNT(*) FROM food_listings")->fetchColumn();
$total_claimed = $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'approved'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$money_raised = $pdo->query("SELECT SUM(amount) FROM money_donations")->fetchColumn() ?: 0;

// Top Donors
$top_donors = $pdo->query("
    SELECT u.username, COUNT(f.id) as posts 
    FROM users u 
    JOIN food_listings f ON u.id = f.donor_id 
    GROUP BY u.id 
    ORDER BY posts DESC 
    LIMIT 5
")->fetchAll();

?>

<div style="display:grid; gap:20px;">
    
    <div class="card" style="text-align:center; padding:40px;">
        <h2 style="color:var(--primary); margin-bottom:10px;">📄 System Performance Report</h2>
        <p style="color:var(--text-muted);">Generated on <?php echo date('F j, Y'); ?></p>
        <button onclick="window.print()" class="btn-action btn-view" style="margin-top:20px; padding:10px 20px; font-size:1rem; cursor:pointer;">🖨️ Print / Save PDF</button>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="card">
            <h3><?php echo $total_food; ?></h3>
            <p style="color:var(--text-muted);">Total Listings</p>
        </div>
        <div class="card">
            <h3><?php echo $total_claimed; ?></h3>
            <p style="color:var(--text-muted);">Meals Distributed</p>
        </div>
        <div class="card">
            <h3><?php echo $total_users; ?></h3>
            <p style="color:var(--text-muted);">Registered Users</p>
        </div>
        <div class="card">
            <h3 style="color:var(--gold);">Rs. <?php echo number_format($money_raised, 2); ?></h3>
            <p style="color:var(--text-muted);">Funds Raised</p>
        </div>
    </div>

    <!-- Details -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <div class="card admin-card">
            <h3 style="margin-bottom:15px;">🏆 Top Donors</h3>
            <table style="width:100%;">
                <?php foreach($top_donors as $d): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid var(--glass-border); text-align: left; color: var(--text-main);"><?php echo htmlspecialchars($d['username']); ?></td>
                    <td style="text-align:right; border-bottom:1px solid var(--glass-border);">
                        <span class="badge" style="background:rgba(16, 185, 129, 0.2); color:#10b981;"><?php echo $d['posts']; ?> Posts</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card admin-card">
            <h3 style="margin-bottom:15px;">ℹ️ About This Report</h3>
            <p style="line-height:1.6; color:var(--text-muted);">
                This report aggregates real-time data from the ZeroWaste-ZeroHunger database. 
                Use this for monthly auditing, NGO compliance checks, and impact assessment presentations.
                <br><br>
                <strong>Confidentiality Notice:</strong> This document contains internal system data. Do not share outside the organization without authorization.
            </p>
        </div>
    </div>

</div>

<!-- Print Styles -->
<style>
    @media print {
        .sidebar, .top-bar, .btn-action, .navbar { display: none !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        body { background: white !important; color: black !important; }
        .card { border: 1px solid #ddd; box-shadow: none; color: black; background: white !important; }
        h2, h3, h4, p, td { color: black !important; }
    }
</style>

</div>
</body>
</html>
