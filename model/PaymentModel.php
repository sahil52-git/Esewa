<?php

require_once __DIR__ . '/../config/db.php';

class PaymentModel {

    private PDO $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    // ─── Create a pending transaction ─────────────────────────────────────────

    /**
     * Insert a new PENDING transaction record before redirecting to eSewa.
     *
     * @return int  The newly inserted transaction ID
     */
    public function createTransaction(array $data): int {
        $sql = "INSERT INTO transactions
                    (product_id, product_name, amount, tax_amount, service_charge,
                     delivery_charge, total_amount, transaction_uuid, status)
                VALUES
                    (:product_id, :product_name, :amount, :tax_amount, :service_charge,
                     :delivery_charge, :total_amount, :transaction_uuid, 'PENDING')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':product_id'      => $data['product_id'],
            ':product_name'    => $data['product_name'],
            ':amount'          => $data['amount'],
            ':tax_amount'      => $data['tax_amount']      ?? 0,
            ':service_charge'  => $data['service_charge']  ?? 0,
            ':delivery_charge' => $data['delivery_charge'] ?? 0,
            ':total_amount'    => $data['total_amount'],
            ':transaction_uuid'=> $data['transaction_uuid'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─── Update transaction after eSewa callback ───────────────────────────────

    /**
     * Mark a transaction as COMPLETE and store eSewa's ref_id.
     */
    public function markComplete(string $transactionUuid, string $refId): bool {
        $sql  = "UPDATE transactions
                 SET status = 'COMPLETE', ref_id = :ref_id, updated_at = NOW()
                 WHERE transaction_uuid = :uuid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':ref_id' => $refId, ':uuid' => $transactionUuid]);
    }

    /**
     * Mark a transaction as FAILED.
     */
    public function markFailed(string $transactionUuid): bool {
        $sql  = "UPDATE transactions
                 SET status = 'FAILED', updated_at = NOW()
                 WHERE transaction_uuid = :uuid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':uuid' => $transactionUuid]);
    }

    // ─── Read operations ───────────────────────────────────────────────────────

    /**
     * Fetch a transaction by its UUID.
     */
    public function getByUuid(string $transactionUuid): ?array {
        $sql  = "SELECT * FROM transactions WHERE transaction_uuid = :uuid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uuid' => $transactionUuid]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Fetch a transaction by its primary ID.
     */
    public function getById(int $id): ?array {
        $sql  = "SELECT * FROM transactions WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Fetch all transactions — latest first (for admin use).
     */
    public function getAll(): array {
        $sql  = "SELECT * FROM transactions ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // ─── Static product catalog (replaces a products table for this demo) ──────

    /**
     * Return all available products.
     */
    public static function getProducts(): array {
        return [
            ['id' => 1, 'name' => 'Wireless Headphones',    'price' => 2500.00, 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80'],
            ['id' => 2, 'name' => 'Mechanical Keyboard',    'price' => 4800.00, 'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&q=80'],
            ['id' => 3, 'name' => 'USB-C Hub 7-in-1',       'price' => 1800.00, 'image' => 'https://images.unsplash.com/photo-1625895197185-efcec01cffe0?w=600&q=80'],
            ['id' => 4, 'name' => 'Laptop Stand Aluminium', 'price' => 3200.00, 'image' => 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=600&q=80'],
            ['id' => 5, 'name' => 'Webcam HD 1080p',        'price' => 5500.00, 'image' => 'https://images.unsplash.com/photo-1587826080692-f439cd0b70da?w=600&q=80'],
            ['id' => 6, 'name' => 'Desk LED Lamp',          'price' => 1200.00, 'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600&q=80'],
        ];
    }

    /**
     * Find a single product by ID.
     */
    public static function getProductById(int $id): ?array {
        foreach (self::getProducts() as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }
}