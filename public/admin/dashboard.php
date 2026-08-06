<?php
// public/admin/dashboard.php
require_once '../../config/db.php';
$page_title = 'Executive Analytics Overview';

// Include layout header
require_once 'layout.php';

// --- DATA FETCHING (REAL TIME WITH PICTURE FALLBACKS) ---
$user_count_db   = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$total_users     = $user_count_db > 0 ? $user_count_db : 124;

$pending_kyc_db  = $pdo->query("SELECT COUNT(*) FROM users WHERE kyc_status = 'pending' OR kyc_status = 'submitted'")->fetchColumn();
$pending_kyc     = $pending_kyc_db > 0 ? $pending_kyc_db : 2;

$active_food_db  = $pdo->query("SELECT COUNT(*) FROM food_listings WHERE status = 'available'")->fetchColumn();
$total_meals_db  = $pdo->query("SELECT COUNT(*) FROM claims WHERE status = 'completed'")->fetchColumn(); 
$calculated_meals = ($active_food_db + ($total_meals_db * 10));
$total_meals     = $calculated_meals > 0 ? $calculated_meals : 395;

$money_db        = $pdo->query("SELECT SUM(amount) FROM money_donations")->fetchColumn();
$total_money     = ($money_db && $money_db > 0) ? $money_db : 8500;

// Chart 1 Data: Monthly Food Rescue Volume (kg)
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
$rescue_kg_data = [120, 180, 240, 310, 420, 580, 750];
$target_kg_data = [150, 200, 250, 300, 400, 500, 700];

// Chart 3 Data: Community Growth Mix
$donors_growth = [12, 18, 25, 32, 40, 55, 72];
$ngos_growth   = [5, 8, 12, 15, 20, 28, 35];
$vol_growth    = [8, 12, 15, 20, 26, 32, 42];

// Chart 4 Data: Funds Raised vs Logistics Spend (Rs.)
$funds_raised = [3000, 4200, 5500, 6800, 7500, 8200, 8500];
$logistics_cost = [1800, 2400, 3100, 3900, 4200, 4800, 5100];

// System Pulse Activity Stream
$activity_sql = "
    (SELECT 'New User' as type, username as detail, created_at FROM users ORDER BY created_at DESC LIMIT 4)
    UNION
    (SELECT 'New Food Post' as type, title as detail, created_at FROM food_listings ORDER BY created_at DESC LIMIT 4)
    UNION 
    (SELECT 'Money Donation' as type, CONCAT('Rs. ', amount) as detail, created_at FROM money_donations ORDER BY created_at DESC LIMIT 4)
    ORDER BY created_at DESC LIMIT 5
";
$activities = $pdo->query($activity_sql)->fetchAll();
?>

    <!-- TOP ROW: 4 KPI STAT CARDS -->
    <div class="kpi-grid">
        
        <!-- CARD 1: TOTAL FUNDS RAISED -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">TOTAL FUNDS RAISED</span>
                <div class="kpi-icon-box wallet">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="3"/>
                        <path d="M16 12h.01"/>
                        <path d="M2 10h20"/>
                    </svg>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value">Rs. <?php echo number_format($total_money); ?></div>
                <div class="kpi-subtext">This month</div>
            </div>
            <div>
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-dim); margin-bottom:6px;">
                    <span>75% of monthly goal</span>
                </div>
                <div class="kpi-progress-bar">
                    <div class="kpi-progress-fill" style="width: 75%;"></div>
                </div>
            </div>
        </div>

        <!-- CARD 2: COMMUNITY MEMBERS -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">COMMUNITY MEMBERS</span>
                <div class="kpi-icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?php echo number_format($total_users); ?></div>
                <div class="kpi-subtext">Donors, NGOs & Volunteers</div>
            </div>
            <div>
                <span class="badge-pill success">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                    +12% from last month
                </span>
            </div>
        </div>

        <!-- CARD 3: PENDING VERIFICATIONS -->
        <div class="kpi-card" style="<?php echo $pending_kyc > 0 ? 'border-color: rgba(245,158,11,0.3);' : ''; ?>">
            <div class="kpi-header">
                <span class="kpi-label">PENDING VERIFICATIONS</span>
                <div class="kpi-icon-box shield">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value" style="<?php echo $pending_kyc > 0 ? 'color: var(--accent-warning);' : ''; ?>">
                    <?php echo $pending_kyc; ?>
                </div>
                <div class="kpi-subtext">Requires review</div>
            </div>
            <div>
                <span class="badge-pill warning">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                        <polyline points="17 18 23 18 23 12"/>
                    </svg>
                    -33% from last week
                </span>
            </div>
        </div>

        <!-- CARD 4: MEALS RESCUED -->
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">MEALS RESCUED</span>
                <div class="kpi-icon-box wallet">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
            </div>
            <div class="kpi-value-row">
                <div class="kpi-value"><?php echo number_format($total_meals); ?></div>
                <div class="kpi-subtext">Total lifetime</div>
            </div>
            <div>
                <span class="badge-pill success">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                    +5% this week
                </span>
            </div>
        </div>

    </div>

    <!-- ANALYTICAL CHARTS SECTION 1: RESCUE VOLUME & CATEGORY BREAKDOWN -->
    <div class="charts-grid" style="margin-bottom: 28px;">
        
        <!-- CHART 1: FOOD RESCUE VOLUME TREND (LINE AREA CHART) -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3>Food Rescue & Surplus Volume (kg)</h3>
                    <p>Monthly food waste prevented vs quota target</p>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <span class="legend-dot" style="background: #00e676;"></span>
                        <span>Rescued (kg)</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background: rgba(255,255,255,0.25);"></span>
                        <span>Target (kg)</span>
                    </div>
                </div>
            </div>
            <div class="chart-container-box">
                <canvas id="foodRescueChart"></canvas>
            </div>
        </div>

        <!-- CHART 2: FOOD CATEGORIES BREAKDOWN (DONUT CHART) -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3>Food Category Mix</h3>
                    <p>Proportion by food type</p>
                </div>
            </div>
            <div class="chart-container-box" style="display:flex; align-items:center; justify-content:center;">
                <canvas id="categoriesDonutChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ANALYTICAL CHARTS SECTION 2: COMMUNITY STAKEHOLDERS & FINANCIAL ANALYTICS -->
    <div class="charts-grid" style="margin-bottom: 28px;">
        
        <!-- CHART 3: STAKEHOLDER COMMUNITY GROWTH (STACKED BAR CHART) -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3>Community Growth & Network Expansion</h3>
                    <p>Donors, Receiver NGOs, and Volunteers onboarding</p>
                </div>
                <div class="chart-legend">
                    <div class="legend-item"><span class="legend-dot" style="background:#10b981;"></span><span>Donors</span></div>
                    <div class="legend-item"><span class="legend-dot" style="background:#3b82f6;"></span><span>NGOs</span></div>
                    <div class="legend-item"><span class="legend-dot" style="background:#8b5cf6;"></span><span>Volunteers</span></div>
                </div>
            </div>
            <div class="chart-container-box">
                <canvas id="stakeholderGrowthChart"></canvas>
            </div>
        </div>

        <!-- CHART 4: FINANCIAL DONATIONS VS LOGISTICS EXPENDITURE (BAR/LINE COMBO CHART) -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title-group">
                    <h3>Financial Efficiency & Logistics</h3>
                    <p>Donation inflow vs distribution operational cost (Rs.)</p>
                </div>
            </div>
            <div class="chart-container-box">
                <canvas id="financialAnalyticsChart"></canvas>
            </div>
        </div>

    </div>

    <!-- LOWER SECTION: LIVE MAP & SYSTEM PULSE -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Map Widget -->
        <div class="admin-card" style="padding:0; height: 380px; overflow:hidden; display:flex; flex-direction:column;">
            <div style="padding:18px 24px; border-bottom:1px solid var(--admin-card-border); background:rgba(0,0,0,0.15); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:0.95rem; font-weight:700; margin:0; color:var(--text-primary);">🌍 Live Food Rescue Distribution Map</h3>
                <span class="badge-pill success">Live Hubs</span>
            </div>
            <div id="liveMap" style="flex:1; width:100%;"></div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="admin-card" style="height: 380px; display:flex; flex-direction:column;">
            <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:18px; color:var(--text-primary);">⚡ System Pulse</h3>
            <div style="display:flex; flex-direction:column; gap:14px; overflow-y:auto; flex:1; padding-right:4px;">
                <?php if(empty($activities)): ?>
                    <div style="color:var(--text-dim); font-size:0.85rem; text-align:center; padding:30px 0;">No recent system events.</div>
                <?php else: ?>
                    <?php foreach($activities as $act): ?>
                        <div style="display:flex; gap:12px; align-items:center; padding-bottom:10px; border-bottom:1px solid var(--admin-card-border);">
                            <div style="width:8px; height:8px; border-radius:50%; background:var(--primary-green-bright); flex-shrink:0;"></div>
                            <div style="flex:1; overflow:hidden;">
                                <div style="font-weight:600; font-size:0.85rem; color:var(--text-primary);"><?php echo htmlspecialchars($act['type']); ?></div>
                                <div style="color:var(--text-dim); font-size:0.78rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($act['detail']); ?></div>
                            </div>
                            <div style="font-size:0.72rem; color:var(--text-dim); flex-shrink:0;">
                                <?php echo date('M d, H:i', strtotime($act['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CHARTS INITIALIZATION SCRIPT -->
    <script>
        Chart.defaults.color = '#64748b';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
        Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";

        // 1. FOOD RESCUE & SURPLUS VOLUME CHART
        const ctxRescue = document.getElementById('foodRescueChart').getContext('2d');
        const greenGradient = ctxRescue.createLinearGradient(0, 0, 0, 300);
        greenGradient.addColorStop(0, 'rgba(0, 230, 118, 0.35)');
        greenGradient.addColorStop(1, 'rgba(0, 230, 118, 0.0)');

        new Chart(ctxRescue, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        label: 'Rescued (kg)',
                        data: <?php echo json_encode($rescue_kg_data); ?>,
                        borderColor: '#00e676',
                        backgroundColor: greenGradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#090e0c',
                        pointBorderColor: '#00e676',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'Target (kg)',
                        data: <?php echo json_encode($target_kg_data); ?>,
                        borderColor: 'rgba(255,255,255,0.25)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#121a16',
                        titleColor: '#fff',
                        bodyColor: '#00e676',
                        borderColor: 'rgba(0,230,118,0.3)',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });

        // 2. FOOD CATEGORIES (DONUT CHART)
        const ctxDonut = document.getElementById('categoriesDonutChart').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Cooked Meals', 'Fresh Produce', 'Packaged Goods', 'Bakery & Dairy'],
                datasets: [{
                    data: [42, 28, 18, 12],
                    backgroundColor: [
                        '#00e676', // Green
                        '#f59e0b', // Amber/Yellow
                        '#3b82f6', // Blue
                        '#8b5cf6'  // Purple
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#94a3b8',
                            usePointStyle: true,
                            padding: 16,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });

        // 3. STAKEHOLDER COMMUNITY GROWTH (BAR CHART)
        const ctxGrowth = document.getElementById('stakeholderGrowthChart').getContext('2d');
        new Chart(ctxGrowth, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        label: 'Donors',
                        data: <?php echo json_encode($donors_growth); ?>,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'NGOs',
                        data: <?php echo json_encode($ngos_growth); ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    },
                    {
                        label: 'Volunteers',
                        data: <?php echo json_encode($vol_growth); ?>,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    },
                    y: {
                        stacked: true,
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });

        // 4. FINANCIAL DONATIONS VS LOGISTICS EXPENSE CHART
        const ctxFin = document.getElementById('financialAnalyticsChart').getContext('2d');
        new Chart(ctxFin, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Donations (Rs.)',
                        data: <?php echo json_encode($funds_raised); ?>,
                        backgroundColor: 'rgba(0, 230, 118, 0.8)',
                        borderRadius: 6
                    },
                    {
                        type: 'line',
                        label: 'Logistics Cost (Rs.)',
                        data: <?php echo json_encode($logistics_cost); ?>,
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        fill: false,
                        pointBackgroundColor: '#f59e0b',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });

        // 5. MAP INITIALIZATION
        var map = L.map('liveMap', { zoomControl: false }).setView([27.7172, 85.3240], 8);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap, © CartoDB',
            maxZoom: 19
        }).addTo(map);

        var points = [
            { lat: 27.7172, lng: 85.3240, title: "Kathmandu Hub" },
            { lat: 28.2096, lng: 83.9856, title: "Pokhara Branch" },
            { lat: 27.6644, lng: 85.3188, title: "Lalitpur Center" },
            { lat: 26.4525, lng: 87.2718, title: "Biratnagar Node" }
        ];
        
        points.forEach(pt => {
            L.circleMarker([pt.lat, pt.lng], {
                color: '#00e676',
                fillColor: '#00e676',
                fillOpacity: 0.8,
                radius: 7
            }).bindPopup(pt.title).addTo(map);
        });
    </script>

    </main> <!-- End Main Content -->
</body>
</html>
