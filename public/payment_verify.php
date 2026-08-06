<?php
require_once '../config/db.php';
require_once '../config/payments.php';
require_once '../src/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verifyPostJson(string $url, array $payload, array $headers = []): array {
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
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $code = (int) $matches[1];
        }
    }

    $data = json_decode((string) $raw, true);
    return ['code' => $code, 'body' => is_array($data) ? $data : []];
}

function renderPaymentSuccess(string $name, float $amount, string $transactionId, string $method, string $currency = 'NPR', string $note = ''): void {
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeMethod = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');
    $safeTransactionId = htmlspecialchars($transactionId, ENT_QUOTES, 'UTF-8');
    $safeNote = htmlspecialchars($note, ENT_QUOTES, 'UTF-8');
    $safeCurrency = htmlspecialchars($currency, ENT_QUOTES, 'UTF-8');
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
        </style>
    </head>
    <body>
        <main class="payment-card">
            <div class="payment-icon">&#10003;</div>
            <h1>Payment Successful</h1>
            <p>Thank you, <?= $safeName ?>. Your payment has been confirmed.</p>
            <?php if ($safeNote !== ''): ?><p class="payment-note"><?= $safeNote ?></p><?php else: ?><p>Your donation has been recorded successfully.</p><?php endif; ?>
            <section class="payment-details" aria-label="Payment details">
                <div><span>Method</span><span><?= $safeMethod ?></span></div>
                <div><span>Amount</span><span><?= $safeCurrency ?> <?= $formattedAmount ?></span></div>
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
                <a class="btn btn-secondary" href="donate_money.php">Try Again</a>
                <a class="btn btn-primary" href="index.php">Go to Home</a>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function saveVerifiedDonation(PDO $pdo, string $method, string $transactionId, string $currency, string $note = ''): array {
    $pending = $_SESSION['pending_donation'] ?? [];
    $amount = isset($pending['amount']) ? (float) $pending['amount'] : 0;
    $name = $pending['donor_name'] ?? 'Anonymous';
    $message = trim(($pending['message'] ?? '') . ($note !== '' ? ' ' . $note : ''));
    $anonymous = $pending['is_anonymous'] ?? 0;
    $userId = $_SESSION['user_id'] ?? null;
    $receiverId = $pending['receiver_id'] ?? null;

    if ($amount <= 0) {
        renderPaymentFailure('Invalid Donation Amount', 'The payment amount was invalid, so the donation could not be recorded.', $method);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO money_donations (donor_name, amount, message, is_anonymous, user_id, receiver_id, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $amount, $message, $anonymous, $userId, $receiverId, $method, $transactionId]);
        addNotification($pdo, ucfirst($method) . ' Donation', $currency . ' ' . number_format($amount, 2) . " from $name | Tx: $transactionId");
    } catch (PDOException $e) {
        if ($e->getCode() !== '23000') {
            renderPaymentFailure('Donation Save Failed', 'The payment was verified, but the donation could not be saved in the database.', ucfirst($method));
        }
    }

    return ['name' => (string) $name, 'amount' => $amount];
}

$method = $_GET['method'] ?? '';

if ($method === 'khalti') {
    $callbackStatus = $_GET['status'] ?? '';
    if ($callbackStatus !== '' && $callbackStatus !== 'Completed') {
        unset($_SESSION['pending_donation'], $_SESSION['khalti_pidx']);
        renderPaymentFailure('Khalti Payment Not Complete', 'Khalti did not complete this transaction. No donation was recorded.', 'Khalti');
    }

    $pidx = $_GET['pidx'] ?? ($_SESSION['khalti_pidx'] ?? '');
    if ($pidx === '' || $pidx !== ($_SESSION['khalti_pidx'] ?? '')) {
        renderPaymentFailure('Khalti Verification Failed', 'The Khalti payment session did not match this browser session.', 'Khalti');
    }
    if (KHALTI_SECRET_KEY === '') {
        renderPaymentFailure('Khalti Verification Failed', 'Khalti secret key is not configured.', 'Khalti');
    }

    $lookup = verifyPostJson(KHALTI_BASE_URL . 'epayment/lookup/', ['pidx' => $pidx], ['Authorization: Key ' . KHALTI_SECRET_KEY]);
    $body = $lookup['body'];
    $expectedPaisa = (int) ($_SESSION['pending_donation']['amount_paisa'] ?? 0);
    $actualPaisa = (int) ($body['total_amount'] ?? 0);

    if (($body['status'] ?? '') !== 'Completed' || $actualPaisa !== $expectedPaisa) {
        renderPaymentFailure('Khalti Verification Failed', 'Khalti lookup did not confirm this payment amount as completed.', 'Khalti');
    }

    $transactionId = (string) ($body['transaction_id'] ?? $pidx);
    $saved = saveVerifiedDonation($pdo, 'khalti', $transactionId, 'NPR');
    unset($_SESSION['pending_donation'], $_SESSION['khalti_pidx']);
    renderPaymentSuccess($saved['name'], $saved['amount'], $transactionId, 'Khalti', 'NPR');
}

if ($method === 'paypal') {
    $orderId = $_GET['order_id'] ?? '';
    $capture = $_SESSION['paypal_capture'] ?? null;
    if ((($_GET['status'] ?? '') !== 'success') || $orderId === '' || $orderId !== ($_SESSION['paypal_order_id'] ?? '') || !is_array($capture)) {
        renderPaymentFailure('PayPal Verification Failed', 'PayPal capture data was not available for this payment session.', 'PayPal');
    }
    if (($capture['status'] ?? '') !== 'COMPLETED') {
        renderPaymentFailure('PayPal Payment Not Complete', 'PayPal did not return a completed capture status.', 'PayPal');
    }

    $captureInfo = $capture['purchase_units'][0]['payments']['captures'][0] ?? [];
    $transactionId = (string) ($captureInfo['id'] ?? $orderId);
    $capturedAmount = (float) ($captureInfo['amount']['value'] ?? 0);
    $expectedAmount = (float) ($_SESSION['pending_donation']['amount'] ?? 0);
    if (abs($capturedAmount - $expectedAmount) > 0.01) {
        renderPaymentFailure('PayPal Amount Mismatch', 'The PayPal captured amount did not match the donation amount.', 'PayPal');
    }

    $saved = saveVerifiedDonation($pdo, 'paypal', $transactionId, PAYPAL_CURRENCY, '(Paid via PayPal ' . PAYPAL_CURRENCY . ')');
    unset($_SESSION['pending_donation'], $_SESSION['paypal_order_id'], $_SESSION['paypal_capture']);
    renderPaymentSuccess($saved['name'], $saved['amount'], $transactionId, 'PayPal', PAYPAL_CURRENCY);
}

renderPaymentFailure('Payment Failed', 'Payment was cancelled or failed. No donation was recorded.', ucfirst($method ?: 'Payment'));
?>
