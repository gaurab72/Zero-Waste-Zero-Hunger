<?php
require_once '../config/db.php';
// Session and functions are handled by navbar.php


// 1. Fetch Top Money Donors (Sum of donations)
$money_stmt = $pdo->query("
    SELECT donor_name, SUM(amount) as total_amount 
    FROM money_donations 
    WHERE is_anonymous = 0 
    GROUP BY donor_name 
    ORDER BY total_amount DESC 
    LIMIT 10
");
$top_money_donors = $money_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare Data for Chart
$money_labels = [];
$money_data = [];
foreach ($top_money_donors as $d) {
    $money_labels[] = $d['donor_name'];
    $money_data[] = $d['total_amount'];
}

// 2. Fetch Top Food Donors (Count of listings)
$food_stmt = $pdo->query("
    SELECT u.username as donor_name, COUNT(f.id) as total_listings 
    FROM food_listings f 
    JOIN users u ON f.donor_id = u.id 
    GROUP BY u.username 
    ORDER BY total_listings DESC 
    LIMIT 10
");
$top_food_donors = $food_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare Data for Chart
$food_labels = [];
$food_data = [];
foreach ($top_food_donors as $d) {
    $food_labels[] = $d['donor_name'];
    $food_data[] = $d['total_listings'];
}

// 3. Fetch Top Volunteers (Count of completed deliveries)
$vol_stmt = $pdo->query("
    SELECT u.username as volunteer_name, COUNT(c.id) as total_deliveries 
    FROM users u 
    LEFT JOIN claims c ON u.id = c.volunteer_id AND c.status = 'completed'
    WHERE u.role = 'volunteer' AND u.availability_status = 'active'
    GROUP BY u.id 
    ORDER BY total_deliveries DESC 
    LIMIT 20
");
$top_volunteers = $vol_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare Data for Chart
$vol_labels = [];
$vol_data = [];
foreach ($top_volunteers as $v) {
    $vol_labels[] = $v['volunteer_name'];
    $vol_data[] = $v['total_deliveries'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Leaderboard | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .leaderboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }
        @media(max-width: 900px) {
            .leaderboard-grid { grid-template-columns: 1fr; }
        }
        .rank-badge {
            display: inline-flex;
            width: 30px;
            height: 30px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            margin-right: 15px;
            color: #fff;
        }
        .rank-1 { background: linear-gradient(135deg, #ffd700, #fdb931); box-shadow: 0 0 10px rgba(255, 215, 0, 0.5); }
        .rank-2 { background: linear-gradient(135deg, #e0e0e0, #bdbdbd); }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #a0522d); }
        .rank-other { background: rgba(255,255,255,0.1); }
        
        .list-item {
            background: rgba(255,255,255,0.03);
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s;
        }
        .list-item:hover {
            transform: translateX(5px);
            background: rgba(255,255,255,0.08);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 60px 0;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 class="text-gradient" style="font-size: 3rem;">Community Heroes</h1>
            <p style="color: var(--text-muted); max-width: 600px; margin: 10px auto;">
                Celebrating the individuals and organizations making a real difference in the fight against hunger and food waste.
            </p>
        </div>

        <div class="leaderboard-grid">
            
            <!-- Money Donors Section -->
            <div>
                <div class="glass-card" style="height: 100%;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                        <h2 style="color: var(--gold, #ffd700);">🏆 Top Contributors</h2>
                        <span style="font-size: 2rem;">💰</span>
                    </div>

                    <!-- Chart -->
                    <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 30px;">
                        <canvas id="moneyChart" height="200"></canvas>
                    </div>

                    <!-- List -->
                    <div style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                        <?php if (empty($top_money_donors)): ?>
                            <p style="text-align:center; color: var(--text-muted);">No financial contributions yet.</p>
                        <?php else: ?>
                            <?php foreach($top_money_donors as $i => $donor): 
                                $rank = $i + 1;
                                $rankClass = $rank <= 3 ? "rank-$rank" : "rank-other";
                            ?>
                            <div class="list-item">
                                <div style="display: flex; align-items: center;">
                                    <span class="rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></span>
                                    <span style="font-weight: 600; font-size: 1.1rem;">
                                        <?php echo htmlspecialchars($donor['donor_name']); ?>
                                        <?php if($rank === 1): ?>
                                            <span style="display:block; font-size: 0.7rem; color: #ffd700;">Mero Bhoj Philanthropist King 👑</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <div style="color: var(--success); font-weight: bold; font-size: 1.2rem;">
                                        Rs. <?php echo number_format($donor['total_amount']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">Total Donated</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Food Donors Section -->
            <div>
                <div class="glass-card" style="height: 100%;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                        <h2 style="color: var(--primary);">🍏 Food Savers</h2>
                        <span style="font-size: 2rem;">🍲</span>
                    </div>

                    <!-- Chart -->
                    <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 30px;">
                        <canvas id="foodChart" height="200"></canvas>
                    </div>

                    <!-- List -->
                    <div style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                        <?php if (empty($top_food_donors)): ?>
                            <p style="text-align:center; color: var(--text-muted);">No food donations yet.</p>
                        <?php else: ?>
                            <?php foreach($top_food_donors as $i => $donor): 
                                $rank = $i + 1;
                                $rankClass = $rank <= 3 ? "rank-$rank" : "rank-other";
                            ?>
                            <div class="list-item">
                                <div style="display: flex; align-items: center;">
                                    <span class="rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></span>
                                    <span style="font-weight: 600; font-size: 1.1rem;">
                                        <?php echo htmlspecialchars($donor['donor_name']); ?>
                                        <?php if($rank === 1): ?>
                                            <span style="display:block; font-size: 0.7rem; color: var(--primary);">Mero Bhoj Food Rescue King 🍳</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <div style="color: var(--primary); font-weight: bold; font-size: 1.2rem;">
                                        <?php echo number_format($donor['total_listings']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">Contributions</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Volunteer Heroes Section -->
            <div>
                <div class="glass-card" style="height: 100%; border-top: 4px solid var(--secondary);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                        <h2 style="color: var(--secondary);">🦸 Volunteer Heroes</h2>
                        <span style="font-size: 2rem;">🚲</span>
                    </div>

                    <!-- Chart -->
                    <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 30px;">
                        <canvas id="volChart" height="200"></canvas>
                    </div>

                    <!-- List -->
                    <div style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                        <?php if (empty($top_volunteers)): ?>
                            <p style="text-align:center; color: var(--text-muted);">No volunteers recorded yet. Join us!</p>
                        <?php else: ?>
                            <?php foreach($top_volunteers as $i => $vol): 
                                $rank = $i + 1;
                                $rankClass = $rank <= 3 ? "rank-$rank" : "rank-other";
                            ?>
                            <div class="list-item">
                                <div style="display: flex; align-items: center;">
                                    <span class="rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></span>
                                    <span style="font-weight: 600; font-size: 1.1rem;">
                                        <?php echo htmlspecialchars($vol['volunteer_name']); ?>
                                        <?php if($rank === 1): ?>
                                            <span style="display:block; font-size: 0.7rem; color: var(--secondary);">Mero Bhoj Logistics Legend 🏆</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <div style="color: var(--secondary); font-weight: bold; font-size: 1.2rem;">
                                        <?php echo number_format($vol['total_deliveries']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">Trips Completed</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        Chart.defaults.color = 'rgba(255, 255, 255, 0.7)';
        Chart.defaults.font.family = "'Poppins', sans-serif";

        // Money Chart (Bar)
        new Chart(document.getElementById('moneyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($money_labels); ?>,
                datasets: [{
                    label: 'Amount Donated (Rs.)',
                    data: <?php echo json_encode($money_data); ?>,
                    backgroundColor: 'rgba(255, 215, 0, 0.6)',
                    borderColor: '#ffd700',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Food Chart (Doughnut)
        new Chart(document.getElementById('foodChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($food_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($food_data); ?>,
                    backgroundColor: [
                        'rgba(0, 255, 136, 0.7)',
                        'rgba(0, 200, 255, 0.7)',
                        'rgba(255, 0, 85, 0.7)',
                        'rgba(255, 215, 0, 0.7)',
                        'rgba(156, 39, 176, 0.7)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: { 
                    legend: { position: 'right', labels: { boxWidth: 15 } }
                }
            }
        });
        // Volunteer Chart (Line)
        new Chart(document.getElementById('volChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($vol_labels); ?>,
                datasets: [{
                    label: 'Deliveries Completed',
                    data: <?php echo json_encode($vol_data); ?>,
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#38bdf8'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>
