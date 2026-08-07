# PayPal Payment Gateway Setup Guide

This document explains how to configure and test PayPal donations for ZeroWaste-ZeroHunger.

## Quick Start (5 minutes)

### 1. Get PayPal API Credentials

1. Go to **[PayPal Developer Dashboard](https://developer.paypal.com/dashboard)** and log in with your PayPal account.
2. Click **"Apps & Credentials"** in the left sidebar.
3. Under **"REST API apps"**, click **"Create App"**.
4. Name your app (e.g., "ZeroWaste-ZeroHunger Sandbox") and click **"Create App"**.
5. On the next screen, you'll see:
   - **Client ID** — Copy this.
   - **Secret** — Click **"Show"** and copy this.

### 2. Configure Credentials

Open [`config/payments.php`](config/payments.php) and replace the placeholder values:

```php
// Line ~14 – Replace these with your sandbox credentials
$paypal_client_id     = getenv('PAYPAL_CLIENT_ID')     ?: 'YOUR_SANDBOX_CLIENT_ID';
$paypal_client_secret = getenv('PAYPAL_CLIENT_SECRET') ?: 'YOUR_SANDBOX_CLIENT_SECRET';
```

Replace:
- `'YOUR_SANDBOX_CLIENT_ID'` with your actual **Client ID**
- `'YOUR_SANDBOX_CLIENT_SECRET'` with your actual **Secret**

Example:
```php
$paypal_client_id     = getenv('PAYPAL_CLIENT_ID')     ?: 'Aa1Bb2Cc3Dd4Ee5Ff6Gg7Hh8Ii9Jj0Kk1Ll2Mm3Nn4Oo5Pp6Qq7Rr8Ss9Tt0Uu1Vv2Ww3Xx';
$paypal_client_secret = getenv('PAYPAL_CLIENT_SECRET') ?: 'EK1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456';
```

### 3. Test with a Sandbox Buyer Account

1. In the **[PayPal Developer Dashboard](https://developer.paypal.com/dashboard)**, go to **"Sandbox" → "Accounts"**.
2. You'll see pre-created test buyer accounts (e.g., `sb-xxxxx@personal.example.com`).
3. When you click **"Pay with PayPal"** on the donation page, log in with one of these sandbox accounts.
4. Use any Visa card number (e.g., `4111111111111111`) with any future expiry date and any 3-digit CVV to complete the payment.

### 4. Verify It Works

1. Log in as a **donor** on your site.
2. Go to **Donate Money** → Select **PayPal**.
3. Enter an amount (in USD — e.g., `5.00`).
4. Click the **PayPal** button.
5. Log in with your sandbox buyer account.
6. Click **"Pay Now"** to approve.
7. You should be redirected to the **Payment Successful** page.
8. The donation is recorded in the `money_donations` table.

## Going Live (Production)

When you're ready to accept real payments:

### 1. Create a Live App

1. In **[PayPal Developer Dashboard](https://developer.paypal.com/dashboard)**, switch to **"Live"** mode (toggle at top).
2. Go to **"Apps & Credentials"** → **"Create App"**.
3. Name it (e.g., "ZeroWaste-ZeroHunger Live").
4. Copy the **Live Client ID** and **Live Secret**.

### 2. Update Configuration

In [`config/payments.php`](config/payments.php):

```php
$paypal_mode = 'live';  // Change from 'sandbox' to 'live'
$paypal_client_id     = 'YOUR_LIVE_CLIENT_ID';
$paypal_client_secret = 'YOUR_LIVE_CLIENT_SECRET';
```

### 3. (Optional) Set Up Webhooks

Webhooks allow PayPal to notify your server of payment events automatically:

1. In the PayPal Developer Dashboard, go to your live app → **"Webhooks"**.
2. Click **"Add Webhook"**.
3. **Webhook URL**: `https://your-domain.com/payment_gateway.php?action=paypal_webhook`
4. **Events to subscribe**: Select at least:
   - `CHECKOUT.ORDER.APPROVED`
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.REFUNDED`
5. Click **"Save"**.
6. Copy the **Webhook ID** and add it to your config for signature verification.

## Troubleshooting

| Symptom | Cause | Solution |
|---------|-------|----------|
| "PayPal is not configured" error | Missing Client ID or Secret | Set credentials in [`config/payments.php`](config/payments.php) |
| PayPal button shows "Not Configured" | Same as above | See instructions above |
| "PayPal authentication failed" | Invalid credentials | Double-check Client ID and Secret from developer dashboard |
| "PayPal could not create the order" | Invalid amount or currency | Ensure amount > 0 and currency is supported by PayPal |
| Blank PayPal popup | Browser blocking popups | Allow popups for your site |
| "Unable to capture PayPal order" | Session expired | Start a new donation |

## File Reference

| File | Purpose |
|------|---------|
| [`config/payments.php`](config/payments.php) | PayPal and Khalti credential configuration |
| [`public/payment_gateway.php`](public/payment_gateway.php) | Server-side PayPal REST API (create order, capture, webhook) |
| [`public/payment_verify.php`](public/payment_verify.php) | Payment verification and donation recording |
| [`public/donate_money.php`](public/donate_money.php) | Frontend donation form with PayPal SDK button |

## Architecture

```
User clicks PayPal button
         │
         ▼
[paypal_sdk.Buttons().createOrder()]
         │  POST /payment_gateway.php?action=paypal_create
         ▼
[Server: Gets OAuth token → Creates Order via PayPal API]
         │  Returns Order ID
         ▼
[PayPal SDK opens checkout popup]
         │  User logs in & approves
         ▼
[paypal_sdk.Buttons().onApprove()]
         │  POST /payment_gateway.php?action=paypal_capture
         ▼
[Server: Captures Order via PayPal API → Verifies amount]
         │  Redirects to /payment_verify.php?method=paypal
         ▼
[payment_verify.php: Saves donation to DB → Shows success page]
```

## Official Documentation

- [PayPal Orders API v2](https://developer.paypal.com/docs/api/orders/v2/)
- [PayPal JavaScript SDK](https://developer.paypal.com/docs/checkout/standard/)
- [PayPal Webhooks](https://developer.paypal.com/docs/api/webhooks/v1/)