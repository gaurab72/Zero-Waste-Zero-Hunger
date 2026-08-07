<?php
// Payment gateway configuration.
// Loads .env file from project root if present, then falls back to server environment variables.

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        // Skip blank lines and comments
        if ($trimmed === '' || $trimmed[0] === '#') {
            continue;
        }
        $eqPos = strpos($trimmed, '=');
        if ($eqPos === false) {
            continue;
        }
        $key   = trim(substr($trimmed, 0, $eqPos));
        $value = trim(substr($trimmed, $eqPos + 1));
        // Strip surrounding quotes if present
        if (strlen($value) >= 2 && (
            ($value[0] === '"'  && $value[-1] === '"') ||
            ($value[0] === "'"  && $value[-1] === "'")
        )) {
            $value = substr($value, 1, -1);
        }
        // Only set if not already set by the real environment (server/docker)
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}


define('ESEWA_PRODUCT_CODE', getenv('ESEWA_PRODUCT_CODE') ?: 'EPAYTEST');
define('ESEWA_SECRET_KEY', getenv('ESEWA_SECRET_KEY') ?: '');
define('ESEWA_PAYMENT_URL', getenv('ESEWA_PAYMENT_URL') ?: 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_STATUS_URL', getenv('ESEWA_STATUS_URL') ?: 'https://rc.esewa.com.np/api/epay/transaction/status/');

define('KHALTI_MODE', getenv('KHALTI_MODE') ?: 'sandbox');
define('KHALTI_SECRET_KEY', getenv('KHALTI_SECRET_KEY') ?: 'live_secret_key_68791341fdd94846a146f0457ff7b455');
define('KHALTI_BASE_URL', KHALTI_MODE === 'live' ? 'https://khalti.com/api/v2/' : 'https://dev.khalti.com/api/v2/');

define('PAYPAL_MODE', getenv('PAYPAL_MODE') ?: 'sandbox');
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'sb');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: '');
define('PAYPAL_BASE_URL', PAYPAL_MODE === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com');
define('PAYPAL_CURRENCY', getenv('PAYPAL_CURRENCY') ?: 'USD');
?>
