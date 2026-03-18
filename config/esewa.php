<?php
/**
 * eSewa Payment Gateway Configuration
 *
 * UAT (Testing) credentials — replace with live credentials for production.
 * Live merchant dashboard: https://merchant.esewa.com.np
 */

// ─── Environment ──────────────────────────────────────────────────────────────
define('ESEWA_ENV', 'UAT'); // 'UAT' for testing | 'LIVE' for production

// ─── UAT (Sandbox) Settings ───────────────────────────────────────────────────
define('ESEWA_UAT_MERCHANT_CODE', 'EPAYTEST');
define('ESEWA_UAT_SECRET_KEY',    '8gBm/:&EnhH.1/q');   // Official UAT secret key
define('ESEWA_UAT_PAYMENT_URL',   'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_UAT_VERIFY_URL',    'https://rc-epay.esewa.com.np/api/epay/transaction/statuscheck');

// ─── LIVE Settings ────────────────────────────────────────────────────────────
define('ESEWA_LIVE_MERCHANT_CODE', 'YOUR_LIVE_MERCHANT_CODE');
define('ESEWA_LIVE_SECRET_KEY',    'YOUR_LIVE_SECRET_KEY');
define('ESEWA_LIVE_PAYMENT_URL',   'https://epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_LIVE_VERIFY_URL',    'https://epay.esewa.com.np/api/epay/transaction/statuscheck');

// ─── Active config based on environment ───────────────────────────────────────
if (ESEWA_ENV === 'LIVE') {
    define('ESEWA_MERCHANT_CODE', ESEWA_LIVE_MERCHANT_CODE);
    define('ESEWA_SECRET_KEY',    ESEWA_LIVE_SECRET_KEY);
    define('ESEWA_PAYMENT_URL',   ESEWA_LIVE_PAYMENT_URL);
    define('ESEWA_VERIFY_URL',    ESEWA_LIVE_VERIFY_URL);
} else {
    define('ESEWA_MERCHANT_CODE', ESEWA_UAT_MERCHANT_CODE);
    define('ESEWA_SECRET_KEY',    ESEWA_UAT_SECRET_KEY);
    define('ESEWA_PAYMENT_URL',   ESEWA_UAT_PAYMENT_URL);
    define('ESEWA_VERIFY_URL',    ESEWA_UAT_VERIFY_URL);
}

// ─── Application URLs ─────────────────────────────────────────────────────────
// Update BASE_URL to match your domain/localhost path
define('BASE_URL', 'http://localhost/practice/Esewa');
define('SUCCESS_URL', BASE_URL . '/views/success.php');
define('FAILURE_URL', BASE_URL . '/views/failure.php'); 