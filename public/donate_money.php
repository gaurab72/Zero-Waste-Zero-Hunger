<?php
require_once '../config/db.php';
require_once '../src/functions.php';
// ─── eSewa v2 Config (test/sandbox) ────────────────────────────────────────
define('ESEWA_PRODUCT_CODE', 'EPAYTEST');
define('ESEWA_SECRET_KEY',   '8gBm/:&EnhH.1/q');
define('ESEWA_PAYMENT_URL',  'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_STATUS_URL',   'https://rc.esewa.com.np/api/epay/transaction/status/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$flash = getFlash();

// Handle Donation Submission
if (!isLoggedIn() || $_SESSION['role'] !== 'donor') {
    setFlash('error', 'Access denied. Please login as a donor.');
    redirect('login.php');
}

// ─── eSewa integration disabled ───────────────────────────────────
// The eSewa payment method has been removed per user request.
// If you need to re‑enable it, restore the constants and UI below.

// And use your real product code & secret key from eSewa merchant portal.

// eSewa signature generation removed – Khalti does not require it.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initiate_pay_btn'])) {
    $name      = !empty($_POST['name'])    ? sanitize($_POST['name'])    : 'Anonymous';
    $amount    = (float) $_POST['amount'];
    $message   = sanitize($_POST['message']);
    $anon      = isset($_POST['anonymous']) ? 1 : 0;
    $receiver_id = !empty($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : null;
    $method    = $_POST['payment_method'] ?? 'card';

    // Store in session to persist across redirects/callbacks
    $_SESSION['pending_donation'] = [
        'donor_name'   => $name,
        'amount'       => $amount,
        'message'      => $message,
        'is_anonymous' => $anon,
        'receiver_id'  => $receiver_id
    ];

    if ($method === 'esewa') {
        // Prepare eSewa request parameters according to eSewa v2 specs
        $txUuid      = '240806-' . time() . '-' . rand(100, 999);
        $totalAmount = number_format($amount, 2, '.', '');
        $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host       = $_SERVER['HTTP_HOST'];
        if ($host === 'localhost:63342') {
            $host = 'localhost';
        }
        $basePath   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $verifyUrl  = $scheme . '://' . $host . $basePath . '/payment_verify.php';
        $successUrl  = $verifyUrl . '?method=esewa';
        $failureUrl  = $verifyUrl . '?method=esewa&status=failed';
        
        $signedFields = 'total_amount,transaction_uuid,product_code';
        $signatureMessage = "total_amount={$totalAmount},transaction_uuid={$txUuid},product_code=" . ESEWA_PRODUCT_CODE;
        $signature = base64_encode(hash_hmac('sha256', $signatureMessage, ESEWA_SECRET_KEY, true));

        $data = [
            'amount'                  => $totalAmount,
            'tax_amount'              => '0',
            'total_amount'            => $totalAmount,
            'transaction_uuid'        => $txUuid,
            'product_code'            => ESEWA_PRODUCT_CODE,
            'product_service_charge'  => '0',
            'product_delivery_charge' => '0',
            'success_url'             => $successUrl,
            'failure_url'             => $failureUrl,
            'signed_field_names'      => $signedFields,
            'signature'               => $signature
        ];

        // Store UUID and amount in session for verification later
        $_SESSION['esewa_tx_uuid']      = $txUuid;
        $_SESSION['esewa_total_amount'] = (float)$totalAmount;

        // Auto‑submit form to eSewa
        echo "<form id='esewa_form' action='" . ESEWA_PAYMENT_URL . "' method='POST'>";
        foreach ($data as $key => $value) {
            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
        echo "</form><script>document.getElementById('esewa_form').submit();</script>";
        exit;
    }
}

// Fetch Verified NGOs
$verified_ngos = $pdo->query("SELECT id, username, location FROM users WHERE role = 'ngo' AND kyc_status = 'approved'")->fetchAll();

// Fetch Top Donors
$top_donors = $pdo->query("SELECT * FROM money_donations WHERE is_anonymous = 0 ORDER BY amount DESC LIMIT 5")->fetchAll();
$recent_donors = $pdo->query("SELECT * FROM money_donations ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Fetch Monthly Stats for Chart
$monthly_stats = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%M') as month, SUM(amount) as total 
    FROM money_donations 
    GROUP BY YEAR(created_at), MONTH(created_at) 
    ORDER BY created_at ASC LIMIT 6
")->fetchAll();

$chart_labels = json_encode(array_column($monthly_stats, 'month'));
$chart_data = json_encode(array_column($monthly_stats, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Money | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>
    <style>
        .payment-method-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        @media (max-width: 480px) {
            .payment-method-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .payment-option {
            background: rgba(255,255,255,0.04);
            border: 1.5px solid var(--glass-border);
            border-radius: 12px;
            padding: 12px 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        .payment-option:hover {
            border-color: var(--primary);
            background: rgba(0, 255, 136, 0.08);
            transform: translateY(-2px);
        }
        .payment-option.active {
            border-color: var(--primary);
            background: rgba(0, 255, 136, 0.15);
            box-shadow: 0 0 16px rgba(0, 255, 136, 0.25);
        }
        .payment-option .logo-box {
            width: 100%;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border-radius: 8px;
            padding: 4px 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        .payment-option .logo-box img {
            max-width: 100%;
            max-height: 34px;
            object-fit: contain;
            display: block;
        }
        .payment-option:hover .logo-box img {
            transform: scale(1.05);
        }
        .payment-option .payment-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }
        .payment-option.active .payment-label,
        .payment-option:hover .payment-label {
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 60px 0;">
        <a href="donate.php" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">
            &larr; Back to Options
        </a>
        <h1 class="text-gradient" style="text-align: center; margin-bottom: 40px;">Support Our Mission</h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
            <!-- Donation Form -->
            <div class="glass-card">
                <h3 style="color: var(--primary); margin-bottom: 20px;">Make a Contribution</h3>
                
                <?php if($flash): ?>
                    <div style="padding: 10px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--success); color: var(--success); background: rgba(16, 185, 129, 0.1);">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="donation-form">
                    <input type="hidden" name="payment_method" id="payment_method" value="khalti">

                    <label class="form-label" style="font-size: 1.1rem; color: #fff; margin-bottom: 12px; display: block;">Select Payment Partner</label>
                    <div class="payment-method-grid" style="grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                        <div class="payment-option" style="padding: 20px; border-radius: 12px;" onclick="selectPayment('khalti', this)">
                            <div class="logo-box" style="background:transparent; box-shadow:none; padding:0; height:auto;">
                                <img src="assets/Images/khalti_logo.jpeg" alt="Khalti" style="max-height: 50px; filter:none; object-fit: contain; mix-blend-mode: multiply; background: white; border-radius: 8px;">
                            </div>
                            <div class="payment-label" style="margin-top: 10px; font-size: 1rem; color: #fff;">Khalti</div>
                        </div>
                        <div class="payment-option" style="padding: 20px; border-radius: 12px;" onclick="selectPayment('esewa', this)">
                            <div class="logo-box" style="background:transparent; box-shadow:none; padding:0; height:auto;">
                                <img src="assets/Images/esewa_logo.png" alt="eSewa" style="max-height: 50px; object-fit: contain;">
                            </div>
                            <div class="payment-label" style="margin-top: 10px; font-size: 1rem; color: #fff;">eSewa</div>
                        </div>
                    </div>

                    <div id="additional-details" style="display: block;">
                        <div class="form-group">
                            <label class="form-label">Support a Specific Organization (Optional)</label>
                            <select name="receiver_id" class="form-input" style="background: var(--bg-input); color: #fff;">
                                <option value="">Donate to Platform (General Fund)</option>
                                <?php foreach($verified_ngos as $ngo): ?>
                                    <option value="<?php echo $ngo['id']; ?>">
                                        <?php echo htmlspecialchars($ngo['username']); ?> (<?php echo htmlspecialchars($ngo['location']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
                                Only verified and active organizations appear in this list.
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Amount (NPR / $)</label>
                            <input type="number" name="amount" class="form-input" placeholder="500.00" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Your Name (Optional)</label>
                            <input type="text" name="name" class="form-input" placeholder="Enter your name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message (Optional)</label>
                            <textarea name="message" class="form-input" rows="3" placeholder="Words of encouragement..."></textarea>
                        </div>
                        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="anonymous" id="anon">
                            <label for="anon" style="cursor: pointer; color: var(--text-muted);">Donate Anonymously</label>
                        </div>
                        <div id="paypal-button-container" style="display: none; margin-bottom: 20px;"></div>
                        <button type="submit" name="initiate_pay_btn" id="donate_btn" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 12px;">Proceed to Pay</button>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 10px; text-align: center;">
                            <span style="color: var(--secondary);">🔒 SSL Secured Payment Gateway</span>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Leaderboard & Stats -->
            <div>
                <!-- Chart Card -->
                <div class="glass-card" style="margin-bottom: 30px;">
                    <h3 style="color: var(--primary); margin-bottom: 20px;">📈 Monthly Trends</h3>
                    <canvas id="donationChart" width="400" height="250"></canvas>
                </div>

                <div class="glass-card" style="margin-bottom: 30px;">
                    <h3 style="color: var(--gold, #ffd700); margin-bottom: 20px;">🏆 Top Heroes</h3>
                    <ul style="list-style: none;">
                        <?php foreach($top_donors as $index => $d): ?>
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <span style="font-weight: bold; color: var(--text-main);">#<?php echo $index+1; ?> <?php echo htmlspecialchars($d['donor_name']); ?></span>
                            <span style="color: var(--success); font-weight: bold;">Rs. <?php echo number_format($d['amount']); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="glass-card">
                    <h3 style="color: var(--secondary); margin-bottom: 20px;">⏱️ Recent Activity</h3>
                    <ul style="list-style: none;">
                        <?php foreach($recent_donors as $d): ?>
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);"><?php echo $d['is_anonymous'] ? 'Anonymous' : htmlspecialchars($d['donor_name']); ?></span>
                                <span style="color: var(--success);">+Rs. <?php echo number_format($d['amount']); ?></span>
                            </div>
                            <?php if(!empty($d['message'])): ?>
                                <p style="font-size: 0.85rem; color: rgba(255,255,255,0.5); font-style: italic;">"<?php echo htmlspecialchars($d['message']); ?>"</p>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Payment Selection Logic
        function selectPayment(method, el) {
            document.getElementById('payment_method').value = method;
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
            el.classList.add('active');

            // Toggle PayPal
            const paypalContainer = document.getElementById('paypal-button-container');
            const nativeBtn = document.getElementById('donate_btn');
            
            if (method === 'paypal') {
                paypalContainer.style.display = 'block';
                nativeBtn.style.display = 'none';
            } else {
                paypalContainer.style.display = 'none';
                nativeBtn.style.display = 'block';
            }
        }

        // Khalti Setup
        const khaltiConfig = {
            "publicKey": "test_public_key_dc74e1d157db4d60b3511f592d3715f5",
            "productIdentity": "1234567890",
            "productName": "Donation",
            "productUrl": "http://localhost/Modern_Food_Waste_System",
            "paymentPreference": ["KHALTI", "EBANKING", "MOBILE_BANKING", "CONNECT_IPS", "SCT"],
            "eventHandler": {
                onSuccess(payload) {
                    window.location.href = `payment_verify.php?method=khalti&status=success&token=${payload.token}`;
                },
                onError(error) { console.log(error); },
                onClose() { console.log("widget is closing"); }
            }
        };
        const checkout = new KhaltiCheckout(khaltiConfig);

        // PayPal Setup
        paypal.Buttons({
            createOrder: function(data, actions) {
                const amount = document.querySelector('input[name="amount"]').value || 10;
                return actions.order.create({
                    purchase_units: [{ amount: { value: amount } }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    window.location.href = "payment_verify.php?method=paypal&status=success";
                });
            }
        }).render('#paypal-button-container');

        // Form Submit Override for Khalti
        document.getElementById('donation-form').onsubmit = function(e) {
            const method = document.getElementById('payment_method').value;
            if (method === 'khalti') {
                e.preventDefault();
                const amount = document.querySelector('input[name="amount"]').value * 100; // Khalti expects paisa
                if (amount > 0) checkout.show({ amount: amount });
            }
        };

        // Chart.js Logic
        const ctx = document.getElementById('donationChart').getContext('2d');
        const labels = <?php echo $chart_labels; ?>;
        const data = <?php echo $chart_data; ?>;

        // Fallback data if empty
        const safeLabels = labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const safeData = data.length ? data : [0, 0, 0, 0, 0, 0];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: safeLabels,
                datasets: [{
                    label: 'Donations (NPR)',
                    data: safeData,
                    backgroundColor: 'rgba(0, 255, 136, 0.5)',
                    borderColor: '#00ff88',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#00ff88'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#888' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#888' }
                    }
                }
            }
        });
    </script>
</body>
</html>
