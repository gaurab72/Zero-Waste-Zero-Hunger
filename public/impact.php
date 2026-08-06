<?php
// Dependencies and session are handled by navbar.php

// Generate some dynamic impact numbers or fetch from DB
$meals_saved = 1240;
$co2_saved = 850;
$donors = 45;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Impact Report | ZeroWaste</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .impact-hero {
            text-align: center;
            padding: 80px 0 60px;
        }
        .stat-number {
            font-size: 3.5rem;
            font-weight: 700;
            margin: 10px 0;
            background: linear-gradient(135deg, var(--text-main), var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .impact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        
        <div class="impact-hero">
            <h1 class="text-gradient" style="font-size: 3.5rem; margin-bottom: 20px;">Measurable Change.</h1>
            <p style="color: var(--text-muted); max-width: 700px; margin: 0 auto; font-size: 1.1rem; line-height: 1.6;">
                Transparency is at our core. Track real-time statistics on how our community is fighting food waste and hunger simultaneously.
            </p>
        </div>

        <!-- Animated Stats -->
        <div class="impact-grid">
            <div class="glass-card" style="text-align: center; border-top: 4px solid var(--primary);">
                <h3 style="color: var(--primary); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px;">Meals Recovered</h3>
                <div class="stat-number" data-target="<?php echo $meals_saved; ?>">0</div>
                <p style="color: var(--text-muted);">Nutritious plates diverted from landfill</p>
            </div>
            
            <div class="glass-card" style="text-align: center; border-top: 4px solid var(--secondary);">
                <h3 style="color: var(--secondary); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px;">CO2 Prevented</h3>
                <div class="stat-number" data-target="<?php echo $co2_saved; ?>">0</div>
                <p style="color: var(--text-muted);">Kilograms of greenhouse emissions saved</p>
            </div>
            
            <div class="glass-card" style="text-align: center; border-top: 4px solid var(--accent);">
                <h3 style="color: var(--accent); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px;">Community Partners</h3>
                <div class="stat-number" data-target="<?php echo $donors; ?>">0</div>
                <p style="color: var(--text-muted);">Restaurants, Hotels & NGOs united</p>
            </div>
        </div>

        <!-- Visuals Section -->
        <div class="glass-card" style="margin-bottom: 80px; padding: 40px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center;">
                <div>
                    <h2 style="font-size: 2rem; margin-bottom: 20px;">The "Waste-to-Worth" Cycle</h2>
                    <p style="color: var(--text-muted); line-height: 1.7; margin-bottom: 20px;">
                        Traditional waste management sends surplus food directly to landfills, generating methane. 
                        Our circular model intercepts this flow at the source.
                    </p>
                    <ul style="list-style: none; color: var(--text-muted);">
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--primary);">✔</span> <strong>Source Separation:</strong> Keeping edibles clean.
                        </li>
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--primary);">✔</span> <strong>Rapid Logistics:</strong> < 2 hour transfer time.
                        </li>
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--primary);">✔</span> <strong>Dignified Access:</strong> Food served with respect.
                        </li>
                    </ul>
                </div>
                <div class="chart-container">
                    <canvas id="impactDoughnut"></canvas>
                    <!-- Center Text Overlay -->
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                        <span style="font-size: 1.5rem; font-weight: bold; color: var(--text-main);">100%</span><br>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Accountable</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div style="text-align: center; margin-bottom: 80px; padding: 60px; background: radial-gradient(circle, rgba(0,255,136,0.1) 0%, rgba(0,0,0,0) 70%); border-radius: 20px; border: 1px solid var(--glass-border);">
            <h2 style="margin-bottom: 20px;">Be Part of the Statistic</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
                Whether you have food to give or time to share, your contribution changes the numbers that matter.
            </p>
            <div style="display: flex; justify-content: center; gap: 15px;">
                <a href="register.php?role=donor" class="btn btn-primary">Start Donating</a>
                <a href="contact.php" class="btn btn-outline">Corporate Partnership</a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="container" style="padding: 40px 0; border-top: 1px solid var(--glass-border); text-align: center; color: var(--text-muted);">
        <p>&copy; <?php echo date('Y'); ?> ZeroWaste-ZeroHunger. Built with 💚 for the planet.</p>
    </footer>

    <script>
        // --- 1. Counter Animation ---
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const speed = 200; // Lower is faster
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target + (target > 100 ? '+' : '');
                }
            };
            updateCount();
        });

        // --- 2. Chart Configuration ---
        const ctx = document.getElementById('impactDoughnut').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Rescued for Meals', 'Composted', 'Recycled Feed'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: [
                        '#00ff88', // Primary Neon Green
                        '#00ccff', // Secondary Neon Blue
                        '#ff0055'  // Accent Neon Pink
                    ],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%', // Thinner ring
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#888',
                            font: { family: "'Inter', sans-serif" },
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false
                    }
                }
            }
        });
    </script>
</body>
</html>
