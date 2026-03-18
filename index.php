<?php

require_once __DIR__ . '/model/PaymentModel.php';

$products = PaymentModel::getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore — eSewa Payments</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="page-wrapper">

    <!-- Nav bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                 TechStore
                <span class="brand-badge">eSewa</span>
            </a>
            <div class="navbar-links">
                <a href="index.php" class="nav-link active">Products</a>
                <a href="https://merchant.esewa.com.np" target="_blank" class="nav-link">Merchant Portal ↗</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">

            <!-- Hero -->
            <section class="hero">
                <div class="hero-eyebrow">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                        <circle cx="6" cy="6" r="6"/>
                    </svg>
                    Secure eSewa Integration
                </div>
                <h1>Shop &amp; Pay with <span>eSewa</span></h1>
                <p class="hero-sub">Browse our catalog and complete purchases instantly using Nepal's leading digital wallet.</p>
            </section>

            <!-- Products -->
            <section class="products-section">
                <div class="section-header">
                    <h2>Available Products</h2>
                    <span class="text-muted" style="font-size:0.85rem;"><?= count($products) ?> items</span>
                </div>

                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image-wrap">
                            <img src="<?= htmlspecialchars($product['image']) ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 loading="lazy">
                            <span class="product-badge">In Stock</span>
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                            <div class="product-price">
                                Rs. <?= number_format($product['price'], 2) ?>
                                <span>NPR</span>
                            </div>
                            <button class="btn btn-primary">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                                Buy Now
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
    </main>
    <footer>
        <div class="container">
            <p>
                &copy; <?= date('Y') ?> TechStore &nbsp;·&nbsp;
                Powered by <a href="https://esewa.com.np" target="_blank">eSewa</a> &nbsp;·&nbsp;
            </p>
        </div>
    </footer>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>