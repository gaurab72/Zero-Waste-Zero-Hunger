<?php
require_once '../config/db.php';
require_once '../config/payments.php';
require_once '../src/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

function paymentJson(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function paymentPostJson(string $url, array $payload, array $headers = [], ?string $basicUser = null, ?string $basicPass = null): array {
    $body = json_encode($payload);
    $headers[] = 'Content-Type: application/json';

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($basicUser !== null && $basicPass !== null) {
            curl_setopt($curl, CURLOPT_USERPWD, $basicUser . ':' . $basicPass);
        }
        $raw = curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
    } else {
        $headerText = implode("\r\n", $headers);
        if ($basicUser !== null && $basicPass !== null) {
            $headerText .= "\r\nAuthorization: Basic " . base64_encode($basicUser . ':' . $basicPass);
        }
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headerText,
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
    return ['code' => $code, 'body' => is_array($data) ? $data : [], 'raw' => $raw, 'error' => $error ?? ''];
}

function paypalAccessToken(): string {
    if (PAYPAL_CLIENT_ID === '' || PAYPAL_CLIENT_ID === 'sb' || PAYPAL_CLIENT_SECRET === '') {
        paymentJson(500, ['error' => 'PayPal sandbox credentials are not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET.']);
    }

    $headers = ['Accept: application/json', 'Accept-Language: en_US'];
    $body = 'grant_type=client_credentials';

    if (function_exists('curl_init')) {
        $curl = curl_init(PAYPAL_BASE_URL . '/v1/oauth2/token');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers) . "\r\nContent-Type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode(PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET),
                'content' => $body,
                'timeout' => 30,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents(PAYPAL_BASE_URL . '/v1/oauth2/token', false, $context);
        $code = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $code = (int) $matches[1];
        }
    }

    $data = json_decode((string) $raw, true);
    if ($code < 200 || $code >= 300 || empty($data['access_token'])) {
        paymentJson(502, ['error' => 'Unable to authenticate with PayPal. Check PayPal credentials and mode.']);
    }

    return $data['access_token'];
}

function readDonationPayload(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }
    return $_POST;
}

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'donor') {
    paymentJson(403, ['error' => 'Please login as a donor before making a payment.']);
}

$action = $_GET['action'] ?? '';
$input = readDonationPayload();

if ($action === 'paypal_create') {
    $amount = isset($input['amount']) ? round((float) $input['amount'], 2) : 0;
    if ($amount <= 0) {
        paymentJson(422, ['error' => 'Please enter a valid PayPal amount.']);
    }

    $name = !empty($input['name']) ? sanitize($input['name']) : 'Anonymous';
    $message = !empty($input['message']) ? sanitize($input['message']) : '';
    $receiverId = !empty($input['receiver_id']) ? (int) $input['receiver_id'] : null;
    $anonymous = !empty($input['anonymous']) ? 1 : 0;
    $reference = 'PP-' . date('ymdHis') . '-' . random_int(1000, 9999);

    $_SESSION['pending_donation'] = [
        'donor_name' => $name,
        'amount' => $amount,
        'message' => $message,
        'is_anonymous' => $anonymous,
        'receiver_id' => $receiverId,
        'payment_method' => 'paypal',
        'paypal_reference' => $reference,
    ];

    $accessToken = paypalAccessToken();
    $response = paymentPostJson(PAYPAL_BASE_URL . '/v2/checkout/orders', [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'reference_id' => $reference,
            'description' => 'ZeroWaste-ZeroHunger donation',
            'amount' => [
                'currency_code' => PAYPAL_CURRENCY,
                'value' => number_format($amount, 2, '.', ''),
            ],
        ]],
    ], ['Authorization: Bearer ' . $accessToken]);

    if ($response['code'] < 200 || $response['code'] >= 300 || empty($response['body']['id'])) {
        paymentJson(502, ['error' => 'PayPal could not create the order.', 'details' => $response['body']]);
    }

    $_SESSION['paypal_order_id'] = $response['body']['id'];
    paymentJson(200, ['id' => $response['body']['id']]);
}

if ($action === 'paypal_capture') {
    $orderId = trim((string) ($input['order_id'] ?? ''));
    if ($orderId === '' || $orderId !== ($_SESSION['paypal_order_id'] ?? '')) {
        paymentJson(422, ['error' => 'PayPal order session did not match.']);
    }

    $accessToken = paypalAccessToken();
    $response = paymentPostJson(PAYPAL_BASE_URL . '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture', [], ['Authorization: Bearer ' . $accessToken]);

    if ($response['code'] < 200 || $response['code'] >= 300) {
        paymentJson(502, ['error' => 'PayPal could not capture the order.', 'details' => $response['body']]);
    }

    $status = $response['body']['status'] ?? '';
    if ($status !== 'COMPLETED') {
        paymentJson(422, ['error' => 'PayPal payment is not completed.', 'status' => $status]);
    }

    $_SESSION['paypal_capture'] = $response['body'];
    paymentJson(200, ['status' => 'COMPLETED', 'redirect' => 'payment_verify.php?method=paypal&status=success&order_id=' . rawurlencode($orderId)]);
}

paymentJson(404, ['error' => 'Unknown payment action.']);
?>
