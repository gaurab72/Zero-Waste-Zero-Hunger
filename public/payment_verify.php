<?php
/**
 * Payment verification handler (eSewa, Khalti, PayPal, etc.)
 */
require_once '../config/db.php';
require_once '../src/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── eSewa v2 Config (test/sandbox) ────────────────────────────────────────
define('ESEWA_PRODUCT_CODE', 'EPAYTEST');
define('ESEWA_SECRET_KEY',   '8gBm/:&EnhH.1/q');
define('ESEWA_PAYMENT_URL',  'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_STATUS_URL',   'https://rc.esewa.com.np/api/epay/transaction/status/');

// ─── Helper: verify HMAC‑SHA256 signature from eSewa response ────────────────
function verifyEsewaResponseSignature(array $data): bool {
    if (empty($data['signed_field_names']) || empty($data['signature'])) {
        return false;
    }
    $fields = explode(',', $data['signed_field_names']);
    $parts = [];
    foreach ($fields as $field) {
        $field = trim($field);
        if (!isset($data[$field])) return false;
        $parts[] = "{$field}={$data[$field]}";
    }
    $message = implode(',', $parts);
    $expectedSig = base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));
    return hash_equals($expectedSig, $data['signature']);
}

// ─── Helper: call eSewa Status Check API ───────────────────────────────────
function checkEsewaStatus(string $txUuid, float $totalAmount): ?array {
    $url = ESEWA_STATUS_URL . '?' . http_build_query([
        'product_code'     => ESEWA_PRODUCT_CODE,
        'total_amount'     => $totalAmount,
        'transaction_uuid' => $txUuid,
    ]);
    $ctx = stream_context_create(['http' => ['timeout' => 15]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    return json_decode($raw, true);
}

// ─── Determine payment method ───────────────────────────────────────────────
function renderPaymentSuccess(string $name, float $amount, string $transactionId, string $method = 'eSewa', string $note = ''): void {
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeMethod = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');
    $safeTransactionId = htmlspecialchars($transactionId, ENT_QUOTES, 'UTF-8');
    $safeNote = htmlspecialchars($note, ENT_QUOTES, 'UTF-8');
    $formattedAmount = number_format($amount, 2);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Successful | ZeroWaste-ZeroHunger</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            body { min-height: 100vh; margin: 0; display: grid; place-items: center; background: #f5fbf7; font-family: Arial, sans-serif; }
            .payment-card { width: min(92%, 520px); background: #fff; border-radius: 18px; padding: 36px; text-align: center; box-shadow: 0 14px 35px rgba(0,0,0,.12); }
            .payment-icon { width: 72px; height: 72px; border-radius: 50%; background: #16a34a; color: #fff; display: grid; place-items: center; font-size: 42px; margin: 0 auto 18px; }
            .payment-card h1 { margin: 0 0 10px; color: #14532d; }
            .payment-card p { color: #4b5563; line-height: 1.6; }
            .payment-note { color: #92400e; background: #fffbeb; border-radius: 10px; padding: 10px 12px; }
            .payment-details { margin: 24px 0; padding: 18px; background: #f0fdf4; border-radius: 12px; text-align: left; }
            .payment-details div { display: flex; justify-content: space-between; gap: 16px; padding: 6px 0; }
            .payment-details span:first-child { color: #4b5563; }
            .payment-details span:last-child { color: #111827; font-weight: 700; text-align: right; }
            .home-btn { display: inline-block; margin-top: 8px; padding: 12px 24px; background: #16a34a; color: #fff; text-decoration: none; border-radius: 10px; font-weight: 700; }
            .home-btn:hover { background: #15803d; }
        </style>
    </head>
    <body>
        <main class="payment-card">
            <div class="payment-icon">&#10003;</div>
            <h1>Payment Successful</h1>
            <p>Thank you, <?= $safeName ?>. Your payment has been confirmed.</p>
            <?php if ($safeNote !== ''): ?>
                <p class="payment-note"><?= $safeNote ?></p>
            <?php else: ?>
                <p>Your donation has been recorded successfully.</p>
            <?php endif; ?>
            <section class="payment-details" aria-label="Payment details">
                <div><span>Method</span><span><?= $safeMethod ?></span></div>
                <div><span>Amount</span><span>Rs. <?= $formattedAmount ?></span></div>
                <div><span>Transaction ID</span><span><?= $safeTransactionId ?></span></div>
            </section>
            <a class="home-btn" href="index.php">Go to Home</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function renderPaymentFailure(string $title, string $message, string $method = 'Payment'): void {
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $safeMethod = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $safeTitle ?> | ZeroWaste-ZeroHunger</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <style>
            body { min-height: 100vh; margin: 0; display: grid; place-items: center; background: #fff7ed; font-family: Arial, sans-serif; }
            .payment-card { width: min(92%, 520px); background: #fff; border-radius: 18px; padding: 36px; text-align: center; box-shadow: 0 14px 35px rgba(0,0,0,.12); }
            .payment-icon { width: 72px; height: 72px; border-radius: 50%; background: #dc2626; color: #fff; display: grid; place-items: center; font-size: 42px; margin: 0 auto 18px; }
            .payment-card h1 { margin: 0 0 10px; color: #7f1d1d; }
            .payment-card p { color: #4b5563; line-height: 1.6; }
            .payment-details { margin: 24px 0; padding: 18px; background: #fef2f2; border-radius: 12px; text-align: left; }
            .payment-details div { display: flex; justify-content: space-between; gap: 16px; padding: 6px 0; }
            .payment-details span:last-child { font-weight: 700; text-align: right; }
            .actions { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
            .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-weight: 700; }
            .btn-primary { background: #16a34a; color: #fff; }
            .btn-secondary { background: #fee2e2; color: #991b1b; }
        </style>
    </head>
    <body>
        <main class="payment-card">
            <div class="payment-icon">!</div>
            <h1><?= $safeTitle ?></h1>
            <p><?= $safeMessage ?></p>
            <section class="payment-details" aria-label="Payment status">
                <div><span>Method</span><span><?= $safeMethod ?></span></div>
                <div><span>Status</span><span>Failed / Cancelled</span></div>
            </section>
            <div class="actions">
                <a class="btn btn-secondary" href="donate_money.php">Try Again</a>
                <a class="btn btn-primary" href="index.php">Go to Home</a>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}



$method = $_GET['method'] ?? '';

// eSewa may append its success payload as "?data=..." even when the success URL
// already contains a query string, producing "?method=esewa?data=...". Normalize
// that malformed callback so the verifier still receives method=esewa and data=... .
if (strpos($method, '?') !== false) {
    [$methodValue, $extraQuery] = explode('?', $method, 2);
    $method = $methodValue;

    parse_str($extraQuery, $extraParams);
    foreach ($extraParams as $key => $value) {
        if (!isset($_GET[$key])) {
            $_GET[$key] = $value;
        }
    }
}

if ($method === '' && isset($_GET['data'])) {
    $method = 'esewa';
}
if ($method === 'esewa') {
    // ─── eSewa failure path ───────────────────────────────────────────────
    if (isset($_GET['status']) && $_GET['status'] === 'failed') {
        unset($_SESSION['esewa_tx_uuid'], $_SESSION['esewa_total_amount']);
        renderPaymentFailure('eSewa Payment Failed', 'The payment was cancelled or failed in eSewa. No donation was recorded.', 'eSewa');
    }

    // ─── eSewa success path ───────────────────────────────────────────────
    $encodedData = $_GET['data'] ?? '';
    if (empty($encodedData)) {
        renderPaymentFailure('eSewa Verification Failed', 'eSewa did not return payment data, so the payment could not be confirmed.', 'eSewa');
    }
    $decoded = base64_decode($encodedData, true);
    if ($decoded === false) {
        renderPaymentFailure('eSewa Verification Failed', 'The eSewa response could not be decoded. Payment was not confirmed by this system.', 'eSewa');
    }
    $esewaData = json_decode($decoded, true);
    if (!is_array($esewaData)) {
        renderPaymentFailure('eSewa Verification Failed', 'The eSewa response format was invalid. Payment was not confirmed by this system.', 'eSewa');
    }
    // Verify signature
    if (!verifyEsewaResponseSignature($esewaData)) {
        renderPaymentFailure('eSewa Verification Failed', 'The eSewa security signature was invalid. Payment was not accepted.', 'eSewa');
    }
    // Compare with session values
    $txUuid = $esewaData['transaction_uuid'] ?? '';
    $txAmount = (float)($esewaData['total_amount'] ?? 0);
    $sessionUuid = $_SESSION['esewa_tx_uuid'] ?? '';
    $sessionAmount = (float)($_SESSION['esewa_total_amount'] ?? 0);
    $hasValidSessionDonation = ($txUuid === $sessionUuid && $txAmount === $sessionAmount);
    // Double‑check via status API
    $statusResp = checkEsewaStatus($txUuid, $txAmount);
    $confirmedStatus = $statusResp['status'] ?? 'UNKNOWN';
    if ($esewaData['status'] !== 'COMPLETE' || $confirmedStatus !== 'COMPLETE') {
        renderPaymentFailure('eSewa Payment Not Complete', 'eSewa did not confirm this transaction as COMPLETE. No donation was recorded.', 'eSewa');
    }
    // All good – record donation
    $amount      = $hasValidSessionDonation ? ($_SESSION['pending_donation']['amount'] ?? $txAmount) : $txAmount;
    $name        = $hasValidSessionDonation ? ($_SESSION['pending_donation']['donor_name'] ?? 'Anonymous') : 'Donor';
    $msg         = $hasValidSessionDonation ? ($_SESSION['pending_donation']['message'] ?? '') : '';
    $anon        = $hasValidSessionDonation ? ($_SESSION['pending_donation']['is_anonymous'] ?? 0) : 0;
    $user_id     = $hasValidSessionDonation ? ($_SESSION['user_id'] ?? null) : null;
    $receiver_id = $hasValidSessionDonation ? ($_SESSION['pending_donation']['receiver_id'] ?? null) : null;
    $txCode      = $esewaData['transaction_code']                ?? $txUuid;
    $paymentSaved = false;
    $paymentNote = $hasValidSessionDonation ? '' : 'Payment was verified by eSewa, but the browser session was not available for donor details. The page is kept here instead of redirecting.';
    if ($amount > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO money_donations (donor_name, amount, message, is_anonymous, user_id, receiver_id, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, ?, 'esewa', ?)");
            $stmt->execute([$name, $amount, $msg, $anon, $user_id, $receiver_id, $txCode]);
            addNotification($pdo, 'eSewa Donation', "Rs. " . number_format($amount) . " from $name | Tx: $txCode");
            $paymentSaved = true;
        } catch (PDOException $e) {
            $paymentNote = 'Payment was verified by eSewa, but it could not be recorded again in the database. Transaction may already exist.';
        }
    } else {
        renderPaymentFailure('Invalid Donation Amount', 'The payment amount was invalid, so the donation could not be recorded.', 'eSewa');
    }
    // Clean up
    unset($_SESSION['pending_donation'], $_SESSION['esewa_tx_uuid'], $_SESSION['esewa_total_amount']);
    renderPaymentSuccess((string)$name, (float)$amount, (string)$txCode, 'eSewa', $paymentNote);
}

// ─── Non‑eSewa methods (Khalti, PayPal, etc.) ────────────────────────────────
$status = $_GET['status'] ?? '';
if ($status === 'success') {
    $amount      = $_SESSION['pending_donation']['amount']      ?? 0;
    $name        = $_SESSION['pending_donation']['donor_name']  ?? 'Anonymous';
    $msg         = $_SESSION['pending_donation']['message']     ?? '';
    $anon        = $_SESSION['pending_donation']['is_anonymous']?? 0;
    $user_id     = $_SESSION['user_id']                         ?? null;
    $receiver_id = $_SESSION['pending_donation']['receiver_id'] ?? null;
    if ($amount > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO money_donations (donor_name, amount, message, is_anonymous, user_id, receiver_id, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $amount, $msg, $anon, $user_id, $receiver_id, $method]);
            addNotification($pdo, 'Money Donation', "Rs. " . number_format($amount) . " from $name via $method");
            setFlash('success', "Thank you! Your donation of Rs. " . number_format($amount) . " via " . ucfirst($method) . " was successful.");
        } catch (PDOException $e) {
            setFlash('error', 'Database error: ' . $e->getMessage());
        }
    } else {
        renderPaymentFailure('Invalid Donation Amount', 'The payment amount was invalid, so the donation could not be recorded.', 'eSewa');
    }
} else {
    renderPaymentFailure('Payment Failed', 'Payment was cancelled or failed. No donation was recorded.', ucfirst($method ?: 'Payment'));
}
redirect('donate_money.php');
?>
