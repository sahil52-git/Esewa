<?php
/**
 * views/failure.php — Payment Failure / Cancellation Handler
 */

require_once __DIR__ . '/../Controller/PaymentController.php';

$controller = new PaymentController();
$data       = $controller->handleFailure();
$reason     = $data['reason'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed — TechStore</title>
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

                    <div class="status-icon failure">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M15 9l-6 6M9 9l6 6"/>
                        </svg>
                    </div>

                    <h2>Payment Failed</h2>
                    <p>Your payment could not be completed. You have not been charged.</p>

                    <?php if ($reason && $reason !== 'unknown'): ?>
                    <div class="alert alert-danger" style="text-align:left;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                        </svg>
                        <span>Reason: <?= htmlspecialchars($reason) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="transaction-detail" style="text-align:left;">
                        <div class="detail-row">
                            <span class="label">What happened?</span>
                            <span class="value" style="color:var(--danger);">Payment was cancelled or declined</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Charged?</span>
                            <span class="value green">No — your balance is safe</span>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:0.75rem;width:100%;margin-top:0.5rem;">
                        <a href="../index.php" class="btn btn-primary btn-lg btn-full">
                            Try Again
                        </a>
                        <a href="../index.php" class="btn btn-outline btn-full">
                            Return to Store
                        </a>
                    </div>

                    <div class="alert alert-info" style="margin-top:1.2rem;text-align:left;font-size:0.82rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        If you believe this is an error, contact eSewa support at
                        <a href="https://esewa.com.np" target="_blank" style="color:var(--accent-blue);">esewa.com.np</a>
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