<?php
/**
 * views/success.php — eSewa Payment Success Callback Handler
 *
 * eSewa redirects here with: ?data=<Base64-encoded-JSON>
 */

require_once __DIR__ . '/../controller/PaymentController.php';

$controller = new PaymentController();
$result     = $controller->verify();

$success     = $result['success'];
$transaction = $result['transaction'];
$message     = $result['message'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Payment Successful' : 'Verification Failed' ?> — TechStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-wrapper">

    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="navbar-brand">⚡ TechStore <span class="brand-badge">eSewa</span></a>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="status-page">
                <div class="status-card">

                    <?php if ($success && $transaction): ?>

                        <!-- ─── SUCCESS ─────────────────────────────────── -->
                        <div class="status-icon success">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>

                        <h2>Payment Successful!</h2>
                        <p>Your order has been confirmed and payment received. Thank you for shopping with us.</p>

                        <div class="transaction-detail">
                            <div class="detail-row">
                                <span class="label">Product</span>
                                <span class="value"><?= htmlspecialchars($transaction['product_name']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Amount Paid</span>
                                <span class="value green">Rs. <?= number_format($transaction['total_amount'], 2) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">eSewa Ref ID</span>
                                <span class="value" style="font-family:monospace;font-size:0.82rem;">
                                    <?= htmlspecialchars($transaction['ref_id'] ?: '—') ?>
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Transaction UUID</span>
                                <span class="value" style="font-family:monospace;font-size:0.75rem;cursor:pointer;"
                                      onclick="copyToClipboard('<?= htmlspecialchars($transaction['transaction_uuid']) ?>')"
                                      title="Click to copy">
                                    <?= htmlspecialchars(substr($transaction['transaction_uuid'], 0, 18)) ?>...
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Status</span>
                                <span class="value green">● COMPLETE</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Date</span>
                                <span class="value"><?= date('d M Y, h:i A', strtotime($transaction['updated_at'])) ?></span>
                            </div>
                        </div>

                        <a href="../index.php" class="btn btn-primary btn-lg btn-full">
                            Continue Shopping
                        </a>

                    <?php else: ?>

                        <!-- ─── VERIFICATION FAILED ─────────────────────── -->
                        <div class="status-icon failure">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M15 9l-6 6M9 9l6 6"/>
                            </svg>
                        </div>

                        <h2>Verification Failed</h2>
                        <p><?= htmlspecialchars($message) ?></p>

                        <div class="alert alert-danger" style="text-align:left;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                            </svg>
                            If money was deducted from your eSewa wallet, please contact support with your transaction details.
                        </div>

                        <a href="../index.php" class="btn btn-outline btn-lg btn-full">
                            Return to Store
                        </a>

                    <?php endif; ?>

                    <div class="security-note" style="margin-top:1.2rem;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Transaction verified with eSewa servers
                    </div>

                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> TechStore · eSewa Payments</p>
        </div>
    </footer>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>