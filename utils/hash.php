<?php
/**
 * Hash Utility — eSewa Signature Generator
 *
 * eSewa v2 API requires a HMAC-SHA256 signature of specific fields
 * encoded in Base64 format.
 */

require_once __DIR__ . '/../config/esewa.php';

/**
 * Generate HMAC-SHA256 Base64 signature.
 *
 * For payment initiation, eSewa signs:
 *   "total_amount,transaction_uuid,product_code"
 *
 * @param string $message  The raw string to sign (comma-separated field values)
 * @return string          Base64-encoded HMAC-SHA256 signature
 */
function generateEsewaSignature(string $message): string {
    $secretKey = ESEWA_SECRET_KEY;

    $hmac = hash_hmac('sha256', $message, $secretKey, true); // raw binary
    return base64_encode($hmac);
}

/**
 * Build the signature message for payment initiation.
 *
 * Field order mandated by eSewa: total_amount, transaction_uuid, product_code
 *
 * @param float  $totalAmount
 * @param string $transactionUuid
 * @param string $productCode      (same as ESEWA_MERCHANT_CODE)
 * @return string
 */
function buildPaymentSignatureMessage(float $totalAmount, string $transactionUuid, string $productCode): string {
    return "total_amount={$totalAmount},transaction_uuid={$transactionUuid},product_code={$productCode}";
}

/**
 * Verify the signature returned by eSewa on success callback.
 *
 * eSewa returns a Base64-encoded JSON in the `data` query param.
 * Decode it, then verify its `signature` field against our re-computed HMAC.
 *
 * @param array $responseData   Decoded eSewa response array
 * @return bool
 */
function verifyEsewaResponseSignature(array $responseData): bool {
    if (empty($responseData['signature']) || empty($responseData['signed_field_names'])) {
        return false;
    }

    $fieldNames = explode(',', $responseData['signed_field_names']);
    $parts = [];

    foreach ($fieldNames as $field) {
        $field = trim($field);
        if (!isset($responseData[$field])) {
            return false; // Required field missing
        }
        $parts[] = "{$field}={$responseData[$field]}";
    }

    $message       = implode(',', $parts);
    $expectedSig   = generateEsewaSignature($message);
    $receivedSig   = $responseData['signature'];

    // Constant-time comparison to prevent timing attacks
    return hash_equals($expectedSig, $receivedSig);
}