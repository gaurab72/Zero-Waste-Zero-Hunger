<?php
require_once '../config/db.php';
require_once '../config/payments.php';
require_once '../src/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    die('Access denied. Admin only.');
}

$result = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_khalti'])) {
    $secretKey = trim($_POST['khalti_secret_key'] ?: KHALTI_SECRET_KEY);
    $mode = trim($_POST['khalti_mode'] ?: KHALTI_MODE);
    $baseUrl = $mode === 'live' ? 'https://khalti.com/api/v2/' : 'https://dev.khalti.com/api/v2/';
    
    $payload = [
        'return_url' => donationBaseUrl() . '/payment_verify.php',
        'website_url' => donationBaseUrl() . '/index.php',
        'amount' => 1000,
        'purchase_order_id' => 'TEST-' . date('ymdHis'),
        'purchase_order_name' => 'Test Donation',
        'customer_info' => [
            'name' => 'Test User',
        ],
    ];
    
    $response = httpPostJson($baseUrl . 'epayment/initiate/', $payload, ['Authorization: Key ' . $secretKey]);
    
    $result = [
        'url' => $baseUrl . 'epayment/initiate/',
        'request' => $payload,
        'headers_sent' => ['Authorization: Key ' . substr($secretKey, 0, 10) . '...'],
        'http_code' => $response['code'],
        'raw_response' => $response['raw'],
        'body' => $response['body'],
        'curl_error' => $response['error'],
    ];
    
    if ($response['code'] >= 200 && $response['code'] < 300 && !empty($response['body']['payment_url'])) {
        $result['status'] = 'SUCCESS';
        $result['payment_url'] = $response['body']['payment_url'];
        $result['pidx'] = $response['body']['pidx'];
    } else {
        $result['status'] = 'FAILED';
        $error = $response['body']['detail'] ?? ($response['body']['error_key'] ?? 'Unknown error');
    }
}

function donationBaseUrl(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $scheme . '://' . $host . $basePath;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Diagnostic Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5fbf7; }
        .card { background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        .success { color: #16a34a; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        pre { background: #f8fafc; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 0.85rem; }
        input, select, button { padding: 10px; margin: 5px 0; border-radius: 6px; border: 1px solid #ddd; }
        button { background: #16a34a; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background: #15803d; }
    </style>
</head>
<body>
    <h1>Khalti Diagnostic Test</h1>
    
    <div class="card">
        <h2>Test Configuration</h2>
        <p>This page tests the Khalti <code>/epayment/initiate/</code> endpoint directly from your server.</p>
        <form method="POST">
            <label><strong>Mode:</strong></label><br>
            <select name="khalti_mode">
                <option value="sandbox" <?php echo KHALTI_MODE === 'sandbox' ? 'selected' : ''; ?>>Sandbox (dev.khalti.com)</option>
                <option value="live" <?php echo KHALTI_MODE === 'live' ? 'selected' : ''; ?>>Live (khalti.com)</option>
            </select><br><br>
            
            <label><strong>Secret Key:</strong></label><br>
            <input type="text" name="khalti_secret_key" value="<?php echo htmlspecialchars(KHALTI_SECRET_KEY); ?>" style="width: 100%; font-family: monospace;"><br><br>
            
            <button type="submit" name="test_khalti">Test Khalti API</button>
        </form>
    </div>
    
    <?php if ($result): ?>
    <div class="card">
        <h2>Test Result: <span class="<?php echo $result['status'] === 'SUCCESS' ? 'success' : 'error'; ?>"><?php echo $result['status']; ?></span></h2>
        
        <?php if ($error): ?>
            <p class="error">Error: <?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($result['payment_url'])): ?>
            <p><a href="<?php echo htmlspecialchars($result['payment_url']); ?>" target="_blank">Open Payment URL (new tab)</a></p>
            <p><strong>PIDX:</strong> <?php echo htmlspecialchars($result['pidx']); ?></p>
        <?php endif; ?>
        
        <h3>HTTP Response Code:</h3>
        <pre><?php echo $result['http_code']; ?></pre>
        
        <h3>Raw Response:</h3>
        <pre><?php echo htmlspecialchars($result['raw_response']); ?></pre>
        
        <h3>Decoded Body:</h3>
        <pre><?php echo htmlspecialchars(print_r($result['body'], true)); ?></pre>
        
        <?php if ($result['curl_error']): ?>
            <h3 class="error">cURL Error:</h3>
            <pre class="error"><?php echo htmlspecialchars($result['curl_error']); ?></pre>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>How to Fix Common Issues</h2>
        <ul>
            <li><strong>"public_key: Invalid key"</strong> = You are using the wrong key type. Use <code>live_secret_key_...</code> from test-admin.khalti.com, NOT <code>live_public_key_...</code></li>
            <li><strong>"Invalid token"</strong> = Your secret key is incorrect or expired. Regenerate it from the dashboard.</li>
            <li><strong>"Authentication credentials were not provided"</strong> = The Authorization header is missing or malformed. It must be exactly: <code>Authorization: Key your_secret_key</code></li>
            <li><strong>Connection timeout</strong> = Your server cannot reach dev.khalti.com. Check firewall/network.</li>
        </ul>
        
        <h3>Your Current Configuration</h3>
        <pre>KHALTI_MODE=<?php echo KHALTI_MODE; ?>
KHALTI_SECRET_KEY=<?php echo KHALTI_SECRET_KEY ? substr(KHALTI_SECRET_KEY, 0, 10) . '...' : '(empty)'; ?>
KHALTI_BASE_URL=<?php echo KHALTI_BASE_URL; ?></pre>
        
        <h3>Sandbox Test Credentials (for payment page)</h3>
        <pre>Khalti ID: 9800000000 (or 9800000001 through 9800000005)
MPIN: 1111
OTP (for merchant login): 987654</pre>
    </div>
    
    <p><a href="donate_money.php">&larr; Back to Donation Page</a></p>
</body>
</html>
