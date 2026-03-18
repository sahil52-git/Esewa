# eSewa Payment Integration

A web-based e-commerce demo integrating eSewa digital payment gateway built with PHP, MySQL, and vanilla JavaScript.

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP 8+
- **Database:** MySQL
- **Payment:** eSewa Payment Gateway v2
- **Server:** Apache (XAMPP)

## Project Structure
```
esewa-payment-project/
├── config/
│   ├── db.php          # Database connection
│   └── esewa.php       # eSewa API credentials & URLs
├── controller/
│   └── PaymentController.php  # Payment initiation & verification
├── model/
│   └── PaymentModel.php       # DB operations & product catalog
├── views/
│   ├── checkout.php    # Order summary & pay button
│   ├── success.php     # Payment success handler
│   └── failure.php     # Payment failure handler
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── utils/
│   └── hash.php        # HMAC-SHA256 signature generator
├── database/
│   └── schema.sql      # Database schema
└── index.php           # Product listing homepage
```

## Setup

**1. Clone the repo**
```bash
git clone https://github.com/yourusername/esewa-payment-project.git
```

**2. Move to XAMPP htdocs**
```
C:\xampp\htdocs\practice\esewa-payment-project
```

**3. Import the database**
- Open phpMyAdmin → SQL tab
- Paste and run the contents of `database/schema.sql`

**4. Configure the project**

In `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'esewa_payment_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

In `config/esewa.php`:
```php
define('BASE_URL', 'http://localhost/practice/esewa-payment-project');
```

**5. Run**

Start Apache and MySQL in XAMPP, then visit:
```
http://localhost/practice/esewa-payment-project
```

## Payment Flow
```
Product Listing → Checkout → eSewa Gateway → Success/Failure
```

1. User selects a product and clicks Buy Now
2. Order summary is shown with the Pay with eSewa button
3. User is redirected to eSewa's payment page
4. After payment, eSewa redirects back with a signed response
5. Signature is verified and transaction is saved to the database

## eSewa UAT Test Credentials

| Field | Value |
|-------|-------|
| eSewa ID | 9806800001 to 9806800005 |
| MPIN | 1122 |
| OTP | 123456 |

> No real money is charged in UAT mode.

## Environment

Currently set to **UAT (Sandbox)**. To switch to production, open `config/esewa.php` and change:
```php
define('ESEWA_ENV', 'LIVE');
```
Then fill in your live merchant code and secret key from the [eSewa Merchant Dashboard](https://merchant.esewa.com.np).

## License

MIT
