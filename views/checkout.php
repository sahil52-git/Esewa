<?php

require_once __DIR__ . '/../model/PaymentModel.php';
require_once __DIR__ . '/../config/esewa.php';
require_once __DIR__ . '/../utils/hash.php';

// Validate product ID
$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    header('Location: ../index.php');
    exit;
}

$product = PaymentModel::getProductById($productId);
if (!$product) {
    header('Location: ../index.php');
    exit;
}

// Amounts
$amount         = (float) $product['price'];
$taxAmount      = 0.00;
$serviceCharge  = 0.00;
$deliveryCharge = 0.00;
$totalAmount    = $amount + $taxAmount + $serviceCharge + $deliveryCharge;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — <?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-wrapper">

    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="navbar-brand">⚡ TechStore <span class="brand-badge">eSewa</span></a>
            <div class="navbar-links">
                <a href="../index.php" class="nav-link">← Back to Products</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">

            <div style="margin-bottom:2rem;">
                <h1 style="font-size:1.8rem;">Checkout</h1>
                <p class="text-muted" style="font-size:0.9rem; margin-top:0.3rem;">Review your order and complete payment securely via eSewa.</p>
            </div>

            <div class="checkout-layout">

                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Order Summary</h3>
                        </div>

                        <div style="display:flex; gap:1.2rem; align-items:flex-start;">
                            <img src="<?= htmlspecialchars($product['image']) ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 style="width:110px;height:80px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                            <div>
                                <div style="font-family:'Sora',sans-serif;font-weight:700;font-size:1.1rem;margin-bottom:0.3rem;">
                                    <?= htmlspecialchars($product['name']) ?>
                                </div>
                                <div style="color:var(--text-muted);font-size:0.85rem;">Product ID: #<?= $product['id'] ?></div>
                                <div style="margin-top:0.6rem;font-size:1.2rem;font-weight:700;color:var(--esewa-green);">
                                    Rs. <?= number_format($amount, 2) ?>
                                </div>
                            </div>
                        </div>

                        <hr style="border:none;border-top:1px solid var(--border);margin:1.5rem 0;">

                        <!-- Price Breakdown -->
                        <div class="summary-row">
                            <span>Item Price</span>
                            <span>Rs. <?= number_format($amount, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span>Rs. <?= number_format($taxAmount, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Service Charge</span>
                            <span>Rs. <?= number_format($serviceCharge, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery</span>
                            <span>Rs. <?= number_format($deliveryCharge, 2) ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Payable</span>
                            <span class="amount">Rs. <?= number_format($totalAmount, 2) ?></span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-2" style="margin-top:1rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        <span>ESewa test credentials will be used. No real money is charged.</span>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Payment Method</h3>
                        </div>

                        <!-- eSewa Badge -->
                        <div style="background:rgba(96,214,105,0.06);border:1px solid rgba(96,214,105,0.2);border-radius:10px;padding:1rem;display:flex;align-items:center;gap:0.8rem;margin-bottom:1.5rem;">
                            <div style="width:40px;height:40px;background:linear-gradient(135deg,#60d669,#3fad47);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1rem;color:#000;">e</div>
                            <div>
                                <div style="font-weight:700;font-size:0.95rem;">eSewa Digital Wallet</div>
                                <div style="color:var(--text-muted);font-size:0.8rem;">Nepal's #1 digital payment</div>
                            </div>
                            <svg style="margin-left:auto;color:var(--esewa-green);" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 12l2 2 4-4M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/>
                            </svg>
                        </div>

                        <form id="esewaPaymentForm" action="../controller/PaymentController.php" method="POST">
                            <input type="hidden" name="action" value="initiate">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                            <!-- Amount Display -->
                            <div style="text-align:center;margin-bottom:1.5rem;">
                                <div style="color:var(--text-muted);font-size:0.85rem;margin-bottom:0.3rem;">Amount to Pay</div>
                                <div style="font-family:'Sora',sans-serif;font-size:2.2rem;font-weight:800;color:var(--esewa-green);">
                                    Rs. <?= number_format($totalAmount, 2) ?>
                                </div>
                            </div>

                            <button type="submit" class="esewa-pay-btn">
                                <span style="font-size:1.2rem;font-weight:900;font-style:italic;"></span>
                                <span>Pay via <span class="esewa-logo-text">eSewa</span></span>
                            </button>
                        </form>

                        <a href="../index.php" class="btn btn-outline btn-full mt-2" style="margin-top:0.8rem;text-align:center;">
                            ← Cancel &amp; Go Back
                        </a>
                    </div>

                    <div class="card" style="margin-top:1rem;padding:1.2rem;">
                        <div style="font-size:0.78rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.8rem;">
                            Test Credentials
                        </div>
                        <div style="font-size:0.82rem;color:var(--text-secondary);line-height:1.8;">
                            <strong style="color:var(--text-primary);">eSewa ID:</strong> 9806800001 – 9806800005<br>
                            <strong style="color:var(--text-primary);">MPIN:</strong> Nepal@123<br>
                            <strong style="color:var(--text-primary);">OTP:</strong> 123456
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> TechStore · eSewa Sandbox</p>
        </div>
    </footer>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>