<?php
require_once '../config/db.php';
require_once '../config/payments.php';
require_once '../src/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash = getFlash();

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'donor') {
    setFlash('error', 'Access denied. Please login as a donor.');
    redirect('login.php');
}

function donationBaseUrl(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $scheme . '://' . $host . $basePath;
}

function donationPostJson(string $url, array $payload, array $headers = []): array {
    $headers[] = 'Content-Type: application/json';
    $body = json_encode($payload);

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => 30,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        $code = 0;
        $error = '';
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $code = (int) $matches[1];
        }
    }

    $data = json_decode((string) $raw, true);
    return ['code' => $code, 'body' => is_array($data) ? $data : [], 'error' => $error ?? ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initiate_pay_btn'])) {
    $name = !empty($_POST['name']) ? sanitize($_POST['name']) : 'Anonymous';
    $amount = isset($_POST['amount']) ? round((float) $_POST['amount'], 2) : 0;
    $message = !empty($_POST['message']) ? sanitize($_POST['message']) : '';
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $receiverId = !empty($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : null;
    $method = $_POST['payment_method'] ?? 'khalti';

    if ($amount <= 0) {
        setFlash('error', 'Please enter a valid donation amount.');
        redirect('donate_money.php');
    }

    $_SESSION['pending_donation'] = [
        'donor_name' => $name,
        'amount' => $amount,
        'message' => $message,
        'is_anonymous' => $anonymous,
        'receiver_id' => $receiverId,
        'payment_method' => $method,
    ];

    if ($method === 'khalti') {
        if ($amount < 10) {
            setFlash('error', 'Khalti requires a minimum donation of Rs. 10.');
            redirect('donate_money.php');
        }
        if (KHALTI_SECRET_KEY === '') {
            setFlash('error', 'Khalti secret key is not configured. Set KHALTI_SECRET_KEY in your server environment.');
            redirect('donate_money.php');
        }

        $paisa = (int) round($amount * 100);
        $purchaseOrderId = 'KHALTI-' . date('ymdHis') . '-' . random_int(1000, 9999);
        $_SESSION['pending_donation']['purchase_order_id'] = $purchaseOrderId;
        $_SESSION['pending_donation']['amount_paisa'] = $paisa;

        $response = donationPostJson(KHALTI_BASE_URL . 'epayment/initiate/', [
            'return_url' => donationBaseUrl() . '/payment_verify.php?method=khalti',
            'website_url' => donationBaseUrl() . '/index.php',
            'amount' => $paisa,
            'purchase_order_id' => $purchaseOrderId,
            'purchase_order_name' => 'ZeroWaste-ZeroHunger Donation',
            'customer_info' => [
                'name' => $name,
            ],
        ], ['Authorization: Key ' . KHALTI_SECRET_KEY]);

        if ($response['code'] >= 200 && $response['code'] < 300 && !empty($response['body']['payment_url']) && !empty($response['body']['pidx'])) {
            $_SESSION['khalti_pidx'] = $response['body']['pidx'];
            redirect($response['body']['payment_url']);
        }

        setFlash('error', 'Khalti could not start the payment. Please check gateway credentials and try again.');
        redirect('donate_money.php');
    }

    if ($method === 'paypal') {
        setFlash('error', 'Please use the PayPal button to complete international payments.');
        redirect('donate_money.php');
    }
}

$verified_ngos = $pdo->query("SELECT id, username, location FROM users WHERE role = 'ngo' AND kyc_status = 'approved'")->fetchAll();
$top_donors = $pdo->query("SELECT * FROM money_donations WHERE is_anonymous = 0 ORDER BY amount DESC LIMIT 5")->fetchAll();
$recent_donors = $pdo->query("SELECT * FROM money_donations ORDER BY created_at DESC LIMIT 5")->fetchAll();
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
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo urlencode(PAYPAL_CLIENT_ID); ?>&currency=<?php echo urlencode(PAYPAL_CURRENCY); ?>"></script>
    <style>
        .payment-method-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .payment-option { background: rgba(255,255,255,0.04); border: 1.5px solid var(--glass-border); border-radius: 12px; padding: 20px; cursor: pointer; text-align: center; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; }
        .payment-option:hover, .payment-option.active { border-color: var(--primary); background: rgba(0, 255, 136, 0.12); transform: translateY(-2px); }
        .payment-option .logo-box { width: 100%; min-height: 54px; display: flex; align-items: center; justify-content: center; }
        .payment-option img { max-width: 150px; max-height: 50px; object-fit: contain; background: #fff; border-radius: 8px; padding: 4px 8px; }
        .payment-option .payment-label { margin-top: 8px; font-size: 1rem; font-weight: 700; color: #fff; }
        @media (max-width: 760px) { .donation-layout { grid-template-columns: 1fr !important; } }
        @media (max-width: 480px) { .payment-method-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 60px 0;">
        <a href="donate.php" style="color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px;">&larr; Back to Options</a>
        <h1 class="text-gradient" style="text-align: center; margin-bottom: 40px;">Support Our Mission</h1>

        <div class="donation-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
            <div class="glass-card">
                <h3 style="color: var(--primary); margin-bottom: 20px;">Make a Contribution</h3>

                <?php if ($flash): ?>
                    <div style="padding: 10px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--success); color: var(--success); background: rgba(16, 185, 129, 0.1);">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="donation-form">
                    <input type="hidden" name="payment_method" id="payment_method" value="khalti">

                    <label class="form-label" style="font-size: 1.1rem; color: #fff; margin-bottom: 12px; display: block;">Select Payment Partner</label>
                    <div class="payment-method-grid">
                        <div class="payment-option active" onclick="selectPayment('khalti', this)">
                            <div class="logo-box"><img src="assets/Images/khalti_logo.jpeg" alt="Khalti"></div>
                            <div class="payment-label">Khalti</div>
                        </div>
                        <div class="payment-option" onclick="selectPayment('paypal', this)">
                            <div class="logo-box"><img src="assets/Images/paypal_logo.svg" alt="PayPal"></div>
                            <div class="payment-label">PayPal</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Support a Specific Organization (Optional)</label>
                        <select name="receiver_id" class="form-input" style="background: var(--bg-input); color: #fff;">
                            <option value="">Donate to Platform (General Fund)</option>
                            <?php foreach ($verified_ngos as $ngo): ?>
                                <option value="<?php echo $ngo['id']; ?>"><?php echo htmlspecialchars($ngo['username']); ?> (<?php echo htmlspecialchars($ngo['location']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Only verified and active organizations appear in this list.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount <span id="amount-currency-label">(NPR)</span></label>
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
                        <input type="checkbox" name="anonymous" id="anon" value="1">
                        <label for="anon" style="cursor: pointer; color: var(--text-muted);">Donate Anonymously</label>
                    </div>
                    <div id="paypal-button-container" style="display: none; margin-bottom: 20px;"></div>
                    <button type="submit" name="initiate_pay_btn" id="donate_btn" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 12px;">Proceed to Pay</button>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 10px; text-align: center;"><span style="color: var(--secondary);">SSL Secured Payment Gateway</span></p>
                </form>
            </div>

            <div>
                <div class="glass-card" style="margin-bottom: 30px;">
                    <h3 style="color: var(--primary); margin-bottom: 20px;">Monthly Trends</h3>
                    <canvas id="donationChart" width="400" height="250"></canvas>
                </div>

                <div class="glass-card" style="margin-bottom: 30px;">
                    <h3 style="color: var(--gold, #ffd700); margin-bottom: 20px;">Top Heroes</h3>
                    <ul style="list-style: none;">
                        <?php foreach ($top_donors as $index => $d): ?>
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <span style="font-weight: bold; color: var(--text-main);">#<?php echo $index + 1; ?> <?php echo htmlspecialchars($d['donor_name']); ?></span>
                            <span style="color: var(--success); font-weight: bold;">Rs. <?php echo number_format($d['amount']); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="glass-card">
                    <h3 style="color: var(--secondary); margin-bottom: 20px;">Recent Activity</h3>
                    <ul style="list-style: none;">
                        <?php foreach ($recent_donors as $d): ?>
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);"><?php echo $d['is_anonymous'] ? 'Anonymous' : htmlspecialchars($d['donor_name']); ?></span>
                                <span style="color: var(--success);">+Rs. <?php echo number_format($d['amount']); ?></span>
                            </div>
                            <?php if (!empty($d['message'])): ?>
                                <p style="font-size: 0.85rem; color: rgba(255,255,255,0.5); font-style: italic;">&quot;<?php echo htmlspecialchars($d['message']); ?>&quot;</p>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectPayment(method, el) {
            document.getElementById('payment_method').value = method;
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
            el.classList.add('active');

            document.getElementById('paypal-button-container').style.display = method === 'paypal' ? 'block' : 'none';
            document.getElementById('donate_btn').style.display = method === 'paypal' ? 'none' : 'block';
            document.getElementById('amount-currency-label').textContent = method === 'paypal' ? '(USD)' : '(NPR)';
        }

        paypal.Buttons({
            createOrder: async function() {
                const form = document.getElementById('donation-form');
                const response = await fetch('payment_gateway.php?action=paypal_create', { method: 'POST', body: new FormData(form) });
                const order = await response.json();
                if (!response.ok) throw new Error(order.error || 'Unable to create PayPal order.');
                return order.id;
            },
            onApprove: async function(data) {
                const response = await fetch('payment_gateway.php?action=paypal_capture', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: data.orderID })
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || 'Unable to capture PayPal order.');
                window.location.href = result.redirect;
            },
            onError: function(error) {
                alert(error.message || 'PayPal payment failed. Please try again.');
            }
        }).render('#paypal-button-container');

        document.getElementById('donation-form').onsubmit = function(e) {
            if (document.getElementById('payment_method').value === 'paypal') {
                e.preventDefault();
                alert('Please use the PayPal button to continue.');
            }
        };

        const ctx = document.getElementById('donationChart').getContext('2d');
        const labels = <?php echo $chart_labels; ?>;
        const data = <?php echo $chart_data; ?>;
        const safeLabels = labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const safeData = data.length ? data : [0, 0, 0, 0, 0, 0];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: safeLabels,
                datasets: [{ label: 'Donations', data: safeData, backgroundColor: 'rgba(0, 255, 136, 0.5)', borderColor: '#00ff88', borderWidth: 1, borderRadius: 5 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        });
    </script>
</body>
</html>
