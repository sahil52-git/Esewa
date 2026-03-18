<?php
/**
 * PaymentController
 *
 * Orchestrates the full eSewa payment lifecycle:
 *   1. initiate()  — build form fields, save PENDING record, output auto-submit form
 *   2. verify()    — decode eSewa callback, verify signature, update DB, redirect
 *
 * Also acts as its own POST entry point when called directly from checkout form.
 */

require_once __DIR__ . '/../config/esewa.php';
require_once __DIR__ . '/../model/PaymentModel.php';
require_once __DIR__ . '/../utils/hash.php';

// ─── Entry point guard ────────────────────────────────────────────────────────
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../index.php');
        exit;
    }
    $action     = $_POST['action'] ?? '';
    $controller = new PaymentController();
    if ($action === 'initiate') {
        $controller->initiate();
    } else {
        header('Location: ../index.php');
        exit;
    }
}

class PaymentController {

    private PaymentModel $model;

    public function __construct() {
        $this->model = new PaymentModel();
    }

    // ─── Step 1: Initiate Payment ──────────────────────────────────────────────

    /**
     * Called from checkout.php when the user clicks "Pay with eSewa".
     * Validates input, creates a DB record, then auto-submits to eSewa.
     */
    public function initiate(): void {
        // Validate POST input
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if (!$productId) {
            $this->redirect(FAILURE_URL . '?reason=invalid_product');
        }

        $product = PaymentModel::getProductById($productId);
        if (!$product) {
            $this->redirect(FAILURE_URL . '?reason=product_not_found');
        }

        // Compute amounts (all in NPR)
        $amount          = (float) $product['price'];
        $taxAmount       = 0.00;
        $serviceCharge   = 0.00;
        $deliveryCharge  = 0.00;
        $totalAmount     = $amount + $taxAmount + $serviceCharge + $deliveryCharge;

        // Generate a unique transaction UUID (UUID v4 style)
        $transactionUuid = $this->generateUuid();

        // Persist PENDING record
        $this->model->createTransaction([
            'product_id'       => $product['id'],
            'product_name'     => $product['name'],
            'amount'           => $amount,
            'tax_amount'       => $taxAmount,
            'service_charge'   => $serviceCharge,
            'delivery_charge'  => $deliveryCharge,
            'total_amount'     => $totalAmount,
            'transaction_uuid' => $transactionUuid,
        ]);

        // Build HMAC-SHA256 signature
        $signatureMessage = buildPaymentSignatureMessage($totalAmount, $transactionUuid, ESEWA_MERCHANT_CODE);
        $signature        = generateEsewaSignature($signatureMessage);

        // Render hidden form and auto-submit to eSewa
        $this->renderEsewaForm([
            'amount'           => $amount,
            'tax_amount'       => $taxAmount,
            'total_amount'     => $totalAmount,
            'transaction_uuid' => $transactionUuid,
            'product_code'     => ESEWA_MERCHANT_CODE,
            'product_service_charge'  => $serviceCharge,
            'product_delivery_charge' => $deliveryCharge,
            'success_url'      => SUCCESS_URL,
            'failure_url'      => FAILURE_URL,
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature'        => $signature,
        ]);
    }

    // ─── Step 2: Verify Payment (Success Callback) ─────────────────────────────

    /**
     * Called from success.php after eSewa redirects back.
     * Decodes the Base64 `data` param, verifies signature, updates DB.
     *
     * @return array  ['success' => bool, 'transaction' => array|null, 'message' => string]
     */
    public function verify(): array {
        $encodedData = $_GET['data'] ?? '';

        if (empty($encodedData)) {
            return ['success' => false, 'transaction' => null, 'message' => 'No payment data received.'];
        }

        // Decode Base64 JSON from eSewa
        $decoded = base64_decode($encodedData, true);
        if ($decoded === false) {
            return ['success' => false, 'transaction' => null, 'message' => 'Invalid encoded data.'];
        }

        $responseData = json_decode($decoded, true);
        if (!is_array($responseData)) {
            return ['success' => false, 'transaction' => null, 'message' => 'Malformed payment response.'];
        }

        // Verify HMAC signature
        if (!verifyEsewaResponseSignature($responseData)) {
            error_log("eSewa signature mismatch for UUID: " . ($responseData['transaction_uuid'] ?? 'N/A'));
            return ['success' => false, 'transaction' => null, 'message' => 'Signature verification failed.'];
        }

        $uuid   = $responseData['transaction_uuid'] ?? '';
        $refId  = $responseData['transaction_code']  ?? ''; // eSewa's own reference
        $status = $responseData['status']             ?? '';

        // Confirm COMPLETE status from eSewa
        if (strtoupper($status) !== 'COMPLETE') {
            $this->model->markFailed($uuid);
            return ['success' => false, 'transaction' => null, 'message' => "Payment status: {$status}"];
        }

        // Optional: re-verify with eSewa status check API
        $transaction = $this->model->getByUuid($uuid);
        if (!$transaction) {
            return ['success' => false, 'transaction' => null, 'message' => 'Transaction record not found.'];
        }

       $verified = true;

        // Update DB to COMPLETE
        $this->model->markComplete($uuid, $refId);
        $transaction = $this->model->getByUuid($uuid); // fresh fetch

        return ['success' => true, 'transaction' => $transaction, 'message' => 'Payment verified successfully.'];
    }

    public function handleFailure(): array {
        // eSewa may pass `data` even on failure in some flows
        $encodedData = $_GET['data'] ?? '';
        $reason      = $_GET['reason'] ?? 'unknown';

        if (!empty($encodedData)) {
            $decoded = base64_decode($encodedData, true);
            if ($decoded) {
                $responseData = json_decode($decoded, true);
                if (isset($responseData['transaction_uuid'])) {
                    $this->model->markFailed($responseData['transaction_uuid']);
                }
            }
        }

        return ['reason' => htmlspecialchars($reason, ENT_QUOTES, 'UTF-8')];
    }

   private function verifyWithEsewaAPI(string $transactionUuid, float $totalAmount): bool {
    $params = http_build_query([
        'product_code'     => ESEWA_MERCHANT_CODE,
        'total_amount'     => $totalAmount,
        'transaction_uuid' => $transactionUuid,
    ]);

    $url = ESEWA_VERIFY_URL . '?' . $params;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,   // ← disabled for localhost
        CURLOPT_SSL_VERIFYHOST => false,   // ← disabled for localhost
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Log for debugging
    error_log("eSewa verify URL: " . $url);
    error_log("eSewa HTTP code: " . $httpCode);
    error_log("eSewa response: " . $response);
    error_log("cURL error: " . $curlError);

    if ($response === false || empty($response)) {
        error_log("eSewa curl failed: " . $curlError);
        return false;
    }

    $data = json_decode($response, true);
    return isset($data['status']) && strtoupper($data['status']) === 'COMPLETE';
}

    private function renderEsewaForm(array $fields): void {
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
        echo '<title>Redirecting to eSewa...</title>';
        echo '<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#0f172a;color:#94a3b8;}';
        echo '.box{text-align:center;}.spinner{width:48px;height:48px;border:4px solid #334155;border-top-color:#60d669;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 1rem;}';
        echo '@keyframes spin{to{transform:rotate(360deg);}}</style></head><body>';
        echo '<div class="box"><div class="spinner"></div><p>Redirecting to eSewa. Please wait...</p>';
        echo '<form id="esewaForm" action="' . htmlspecialchars(ESEWA_PAYMENT_URL, ENT_QUOTES) . '" method="POST">';

        foreach ($fields as $name => $value) {
            printf('<input type="hidden" name="%s" value="%s">',
                htmlspecialchars($name, ENT_QUOTES),
                htmlspecialchars((string)$value, ENT_QUOTES)
            );
        }

        echo '</form></div>';
        echo '<script>document.getElementById("esewaForm").submit();</script>';
        echo '</body></html>';
        exit;
    }

    /**
     * Generate a UUID v4.
     */
    private function generateUuid(): string {
        $data    = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Safe redirect helper.
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}