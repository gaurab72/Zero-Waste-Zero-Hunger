<?php
// Payment gateway configuration.
// Set these values in your web server environment for real sandbox/live tests.

define('KHALTI_MODE', getenv('KHALTI_MODE') ?: 'sandbox');
define('KHALTI_SECRET_KEY', getenv('KHALTI_SECRET_KEY') ?: '');
define('KHALTI_BASE_URL', KHALTI_MODE === 'live' ? 'https://khalti.com/api/v2/' : 'https://dev.khalti.com/api/v2/');

define('PAYPAL_MODE', getenv('PAYPAL_MODE') ?: 'sandbox');
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: 'sb');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') ?: '');
define('PAYPAL_BASE_URL', PAYPAL_MODE === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com');
define('PAYPAL_CURRENCY', getenv('PAYPAL_CURRENCY') ?: 'USD');
?>
