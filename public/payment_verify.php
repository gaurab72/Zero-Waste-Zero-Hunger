<?php
require_once '../config/db.php';
require_once '../config/payments.php';
require_once '../src/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verifyPostJson(string $url, array $payload, array $headers = []): array {
    return httpPostJson($url, $payload, $headers);
}

function renderPaymentSuccess(string $name, float $amount, string $transactionId, string $method, string $currency = 'NPR', string $note = ''): void {
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeMethod = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');
    $safeTransactionId = htmlspecialchars($transactionId, ENT_QUOTES, 'UTF-8');
    $safeNote = htmlspecialchars($note, ENT_QUOTES, 'UTF-8');
    $safeCurrency = htmlspecialchars($currency, ENT_QUOTES, 'UTF-8');
    $formattedAmount = number_format($amount, 2);

    setFlash('success', "Thank you, {$safeName}! Your {$safeMethod} donation of {$safeCurrency} {$formattedAmount} has been recorded successfully. (Tx ID: {$safeTransactionId})");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="refresh" content="3;url=index.php">
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
            .redirect-hint { margin-top: 15px; font-size: 0.85rem; color: #6b7280; }
        </style>
    </head>
    <body>
        <main class="payment-card">
            <div class="payment-icon">&#10003;</div>
            <h1>Payment Successful!</h1>
            <p>Thank you, <?= $safeName ?>. Your donation has been confirmed.</p>
            <?php if ($safeNote !== ''): ?><p class="payment-note"><?= $safeNote ?></p><?php endif; ?>
            <section class="payment-details" aria-label="Payment details">
                <div><span>Method</span><span><?= $safeMethod ?></span></div>
                <div><span>Amount</span><span><?= $safeCurrency ?> <?= $formattedAmount ?></span></div>
                <div><span>Transaction ID</span><span><?= $safeTransactionId ?></span></div>
            </section>
            <a class="home-btn" href="index.php">Return to Home Page</a>
            <p class="redirect-hint">Redirecting to home page in <span id="timer">3</span> seconds...</p>
        </main>
        <script>
            let sec = 3;
            const timerEl = document.getElementById('timer');
            setInterval(() => {
                sec--;
                if (sec >= 0 && timerEl) timerEl.textContent = sec;
                if (sec <= 0) window.location.href = 'index.php';
            }, 1000);
        </script>
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
            .actions { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
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
            <p><strong>Method:</strong> <?= $safeMethod ?></p>
            <div class="actions">
                <a class="btn btn-primary" href="index.php">Go to Home Page</a>
                <a class="btn btn-secondary" href="donate_money.php">Try Again</a>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function saveVerifiedDonation(PDO $pdo, string $method, string $transactionId, string $currency, string $note = '', float $fallbackAmount = 0.0, string $fallbackName = 'Donor'): array {
    $pending   = $_SESSION['pending_donation'] ?? [];
    $amount    = isset($pending['amount']) && (float) $pending['amount'] > 0
                     ? (float) $pending['amount']
                     : $fallbackAmount;
    $name      = !empty($pending['donor_name']) ? $pending['donor_name'] : $fallbackName;
    $message   = trim(($pending['message'] ?? '') . ($note !== '' ? ' ' . $note : ''));
    $anonymous = $pending['is_anonymous'] ?? 0;
    $userId    = $_SESSION['user_id'] ?? null;
    $receiverId = $pending['receiver_id'] ?? null;

    // If amount is still 0 after all fallbacks, it means the callback data
    // also had no amount — that shouldn't happen on a real eSewa/Khalti COMPLETE response.
    if ($amount <= 0) {
        error_log("saveVerifiedDonation: amount is 0 for $method / $transactionId. Session pending: " . json_encode($pending));
        renderPaymentFailure(
            'Invalid Donation Amount',
            'The payment amount could not be determined. Please contact support with Transaction ID: ' . htmlspecialchars($transactionId),
            ucfirst($method)
        );
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO money_donations (donor_name, amount, message, is_anonymous, user_id, receiver_id, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $amount, $message, $anonymous, $userId, $receiverId, $method, $transactionId]);
        addNotification($pdo, ucfirst($method) . ' Donation', $currency . ' ' . number_format($amount, 2) . " from $name | Tx: $transactionId");
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            // Duplicate transaction ID — payment already recorded, still show success to user
            error_log("Duplicate transaction_id (already recorded): $transactionId for method $method");
            return ['name' => (string) $name, 'amount' => $amount];
        }
        error_log('saveVerifiedDonation DB error: ' . $e->getMessage());
        renderPaymentFailure(
            'Donation Save Failed',
            'The payment was verified, but the donation could not be saved in the database. Error: ' . $e->getMessage(),
            ucfirst($method)
        );
    }

    return ['name' => (string) $name, 'amount' => $amount];
}

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

$method = $_GET['method'] ?? '';

if (isset($_GET['data'])) {
    $method = 'esewa';
} elseif ($method === '' && isset($_GET['status']) && $_GET['status'] === 'failed' && (isset($_SESSION['esewa_tx_uuid']) || isset($_SESSION['esewa_total_amount']))) {
    $method = 'esewa';
} elseif ($method === '' && isset($_GET['pidx'])) {
    $method = 'khalti';
}

if ($method === 'esewa') {
    if (isset($_GET['status']) && $_GET['status'] === 'failed') {
        unset($_SESSION['esewa_tx_uuid'], $_SESSION['esewa_total_amount']);
        renderPaymentFailure('eSewa Payment Failed', 'The payment was cancelled or failed in eSewa. No donation was recorded.', 'eSewa');
    }

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

    // Signature verification — skip only if secret key is not configured (dev mode).
    // In production ESEWA_SECRET_KEY must always be set.
    if (ESEWA_SECRET_KEY !== '' && !verifyEsewaResponseSignature($esewaData)) {
        error_log('eSewa signature mismatch. Received: ' . json_encode($esewaData));
        renderPaymentFailure('eSewa Verification Failed', 'The eSewa security signature was invalid. Payment was not accepted.', 'eSewa');
    }

    $txUuid    = $esewaData['transaction_uuid'] ?? '';
    $txAmount  = (float) str_replace(',', '', $esewaData['total_amount'] ?? '0');

    // Confirm via eSewa status API (sandbox may time-out — allow graceful fallback)
    $statusResp      = checkEsewaStatus($txUuid, $txAmount);
    $confirmedStatus = $statusResp['status'] ?? '';

    // Accept: callback says COMPLETE AND (status API agrees OR status API timed out/failed)
    $callbackStatus = $esewaData['status'] ?? '';
    $callbackOk = ($callbackStatus === 'COMPLETE');
    $apiOk      = ($confirmedStatus === 'COMPLETE' || $confirmedStatus === '');

    error_log("eSewa verify: uuid=$txUuid amount=$txAmount callbackStatus=$callbackStatus apiStatus=$confirmedStatus");

    if (!$callbackOk || !$apiOk) {
        renderPaymentFailure(
            'eSewa Payment Not Complete',
            'eSewa returned status: ' . htmlspecialchars($callbackStatus ?: 'Unknown') .
            ' | API status: ' . htmlspecialchars($confirmedStatus ?: 'N/A') . '. No donation was recorded.',
            'eSewa'
        );
    }

    // Use callback amount as authoritative amount (covers session-lost scenario)
    $txCode       = $esewaData['transaction_code'] ?? $txUuid;
    $fallbackName = $_SESSION['pending_donation']['donor_name'] ?? 'eSewa Donor';
    $saved = saveVerifiedDonation($pdo, 'esewa', $txCode, 'NPR', '', $txAmount, $fallbackName);
    unset($_SESSION['pending_donation'], $_SESSION['esewa_tx_uuid'], $_SESSION['esewa_total_amount']);
    renderPaymentSuccess($saved['name'], $saved['amount'], $txCode, 'eSewa', 'NPR');
}

if ($method === 'khalti') {
    $callbackStatus = $_GET['status'] ?? '';
    if ($callbackStatus === 'User canceled' || $callbackStatus === 'Canceled' || $callbackStatus === 'User%20canceled') {
        unset($_SESSION['pending_donation'], $_SESSION['khalti_pidx']);
        renderPaymentFailure('Khalti Payment Canceled', 'The payment was canceled by the user. No donation was recorded.', 'Khalti');
    }

    $pidx = $_GET['pidx'] ?? ($_SESSION['khalti_pidx'] ?? '');
    if ($pidx === '') {
        renderPaymentFailure('Khalti Verification Failed', 'No payment identifier (pidx) was provided.', 'Khalti');
    }
    if (KHALTI_SECRET_KEY === '') {
        renderPaymentFailure('Khalti Verification Failed', 'Khalti secret key is not configured. Get sandbox credentials from https://test-admin.khalti.com and set KHALTI_SECRET_KEY in .env or server environment.', 'Khalti');
    }

    $lookup = verifyPostJson(KHALTI_BASE_URL . 'epayment/lookup/', ['pidx' => $pidx], ['Authorization: Key ' . KHALTI_SECRET_KEY]);
    $lookupCode = $lookup['code'];
    $body = $lookup['body'];
    
    error_log('Khalti lookup pidx=' . $pidx . ' response_code=' . $lookupCode . ' body=' . $lookup['raw']);

    if ($lookupCode < 200 || $lookupCode >= 300) {
        $errorDetail = $body['detail'] ?? ($body['error_key'] ?? 'Unknown error');
        renderPaymentFailure('Khalti Lookup Failed', "Khalti verification API returned HTTP $lookupCode. Detail: $errorDetail", 'Khalti');
    }

    $status = $body['status'] ?? '';
    if ($status !== 'Completed' && $status !== 'Pending') {
        renderPaymentFailure('Khalti Payment Not Complete', 'Khalti lookup did not confirm this transaction as Completed. Status: ' . ($status ?: 'Unknown'), 'Khalti');
    }

    $actualPaisa = (int) ($body['total_amount'] ?? 0);
    $expectedPaisa = (int) ($_SESSION['pending_donation']['amount_paisa'] ?? $actualPaisa);

    if ($expectedPaisa > 0 && $actualPaisa !== $expectedPaisa) {
        renderPaymentFailure('Khalti Amount Mismatch', "Expected NPR " . ($expectedPaisa / 100) . " but Khalti confirmed NPR " . ($actualPaisa / 100) . ". No donation was recorded.", 'Khalti');
    }

    if (empty($_SESSION['pending_donation'])) {
        $_SESSION['pending_donation'] = [
            'donor_name' => $body['customer_info']['name'] ?? 'Donor',
            'amount' => $actualPaisa / 100,
            'message' => 'Donation via Khalti',
            'is_anonymous' => 0,
            'payment_method' => 'khalti',
        ];
    }

    $transactionId  = (string) ($body['transaction_id'] ?? ($body['idx'] ?? $pidx));
    $fallbackAmountNpr = $actualPaisa > 0 ? ($actualPaisa / 100) : 0.0;
    $fallbackName   = $body['customer_info']['name'] ?? 'Khalti Donor';
    $saved = saveVerifiedDonation($pdo, 'khalti', $transactionId, 'NPR', '', $fallbackAmountNpr, $fallbackName);
    unset($_SESSION['pending_donation'], $_SESSION['khalti_pidx']);
    renderPaymentSuccess($saved['name'], $saved['amount'], $transactionId, 'Khalti', 'NPR');
}

if ($method === 'paypal') {
    $orderId = $_GET['order_id'] ?? ($_POST['order_id'] ?? '');
    $capture = $_SESSION['paypal_capture'] ?? null;
    
    if (!$capture && !empty($_POST['capture_details'])) {
        $capture = json_decode($_POST['capture_details'], true);
        $_SESSION['paypal_order_id'] = $orderId;
        $_SESSION['paypal_capture'] = $capture;
    }

    $statusParam = $_GET['status'] ?? ($_POST['status'] ?? '');
    if ($statusParam !== 'success' || $orderId === '' || !is_array($capture)) {
        renderPaymentFailure('PayPal Verification Failed', 'PayPal capture data was not available for this payment session.', 'PayPal');
    }

    if (($capture['status'] ?? '') !== 'COMPLETED') {
        renderPaymentFailure('PayPal Payment Not Complete', 'PayPal did not return a completed capture status.', 'PayPal');
    }

    $captureInfo = $capture['purchase_units'][0]['payments']['captures'][0] ?? [];
    $transactionId = (string) ($captureInfo['id'] ?? ($capture['id'] ?? $orderId));
    $capturedAmount = (float) ($captureInfo['amount']['value'] ?? ($capture['purchase_units'][0]['amount']['value'] ?? 0));
    
    $expectedAmount = (float) ($_SESSION['pending_donation']['amount'] ?? $capturedAmount);
    $expectedCents = (int) round($expectedAmount * 100);
    $capturedCents = (int) round($capturedAmount * 100);

    if ($expectedCents > 0 && $capturedCents !== $expectedCents) {
        renderPaymentFailure('PayPal Amount Mismatch', 'The PayPal captured amount did not match the donation amount.', 'PayPal');
    }

    if (empty($_SESSION['pending_donation'])) {
        $_SESSION['pending_donation'] = [
            'donor_name' => 'Donor',
            'amount' => $capturedAmount,
            'message' => 'Donation via PayPal',
            'is_anonymous' => 0,
            'payment_method' => 'paypal',
        ];
    }

    $saved = saveVerifiedDonation($pdo, 'paypal', $transactionId, PAYPAL_CURRENCY, '(Paid via PayPal ' . PAYPAL_CURRENCY . ')');
    unset($_SESSION['pending_donation'], $_SESSION['paypal_order_id'], $_SESSION['paypal_capture']);
    renderPaymentSuccess($saved['name'], $saved['amount'], $transactionId, 'PayPal', PAYPAL_CURRENCY);
}

renderPaymentFailure('Payment Failed', 'Payment was cancelled or failed. No donation was recorded.', ucfirst($method ?: 'Payment'));
