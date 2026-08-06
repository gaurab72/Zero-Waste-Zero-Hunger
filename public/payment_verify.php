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
$method = $_GET['method'] ?? '';
if ($method === 'esewa') {
    // ─── eSewa failure path ───────────────────────────────────────────────
    if (isset($_GET['status']) && $_GET['status'] === 'failed') {
        unset($_SESSION['esewa_tx_uuid'], $_SESSION['esewa_total_amount']);
        setFlash('error', 'eSewa payment was cancelled or failed. Please try again.');
        redirect('donate_money.php');
        exit;
    }

    // ─── eSewa success path ───────────────────────────────────────────────
    $encodedData = $_GET['data'] ?? '';
    if (empty($encodedData)) {
        setFlash('error', 'eSewa returned an invalid response.');
        redirect('donate_money.php');
        exit;
    }
    $decoded = base64_decode($encodedData, true);
    if ($decoded === false) {
        setFlash('error', 'Failed to decode eSewa response.');
        redirect('donate_money.php');
        exit;
    }
    $esewaData = json_decode($decoded, true);
    if (!is_array($esewaData)) {
        setFlash('error', 'Malformed eSewa response.');
        redirect('donate_money.php');
        exit;
    }
    // Verify signature
    if (!verifyEsewaResponseSignature($esewaData)) {
        setFlash('error', 'Signature verification failed.');
        redirect('donate_money.php');
        exit;
    }
    // Compare with session values
    $txUuid = $esewaData['transaction_uuid'] ?? '';
    $txAmount = (float)($esewaData['total_amount'] ?? 0);
    $sessionUuid = $_SESSION['esewa_tx_uuid'] ?? '';
    $sessionAmount = (float)($_SESSION['esewa_total_amount'] ?? 0);
    if ($txUuid !== $sessionUuid || $txAmount !== $sessionAmount) {
        setFlash('error', 'Transaction details mismatch.');
        redirect('donate_money.php');
        exit;
    }
    // Double‑check via status API
    $statusResp = checkEsewaStatus($txUuid, $txAmount);
    $confirmedStatus = $statusResp['status'] ?? 'UNKNOWN';
    if ($esewaData['status'] !== 'COMPLETE' || $confirmedStatus !== 'COMPLETE') {
        setFlash('error', 'eSewa transaction not complete.');
        redirect('donate_money.php');
        exit;
    }
    // All good – record donation
    $amount      = $_SESSION['pending_donation']['amount']       ?? $txAmount;
    $name        = $_SESSION['pending_donation']['donor_name']   ?? 'Anonymous';
    $msg         = $_SESSION['pending_donation']['message']      ?? '';
    $anon        = $_SESSION['pending_donation']['is_anonymous'] ?? 0;
    $user_id     = $_SESSION['user_id']                         ?? null;
    $receiver_id = $_SESSION['pending_donation']['receiver_id']  ?? null;
    $txCode      = $esewaData['transaction_code']                ?? $txUuid;
    if ($amount > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO money_donations (donor_name, amount, message, is_anonymous, user_id, receiver_id, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, ?, 'esewa', ?)");
            $stmt->execute([$name, $amount, $msg, $anon, $user_id, $receiver_id, $txCode]);
            addNotification($pdo, 'eSewa Donation', "Rs. " . number_format($amount) . " from $name | Tx: $txCode");
            setFlash('success', "✅ Thank you $name! Your eSewa donation of Rs. " . number_format($amount) . " was successful.");
        } catch (PDOException $e) {
            setFlash('error', 'Database error: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Invalid donation amount.');
    }
    // Clean up
    unset($_SESSION['pending_donation'], $_SESSION['esewa_tx_uuid'], $_SESSION['esewa_total_amount']);
    redirect('donate_money.php');
    exit;
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
        setFlash('error', 'Invalid donation amount.');
    }
} else {
    setFlash('error', 'Payment was cancelled or failed.');
}
redirect('donate_money.php');
?>
