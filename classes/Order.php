<?php
/**
 * MAB Shop - Order Model
 * Order placement, tracking, and invoice generation
 */

declare(strict_types=1);

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Create new order from cart
     */
    public function create(array $data): array
    {
        $cart = new Cart();
        $summary = $cart->getSummary($data['coupon_id'] ?? null);

        if (empty($summary['items'])) {
            return ['success' => false, 'message' => 'Cart is empty.'];
        }

        $this->db->beginTransaction();
        try {
            $orderNumber = generateOrderNumber();
            $shippingAddress = json_encode($data['shipping_address']);

            $stmt = $this->db->prepare('INSERT INTO orders (order_number, user_id, guest_email, subtotal, tax_amount, shipping_amount, discount_amount, total_amount, coupon_id, shipping_address, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $orderNumber,
                $data['user_id'] ?? null,
                $data['guest_email'] ?? null,
                $summary['subtotal'],
                $summary['tax'],
                $summary['shipping'],
                $summary['discount'],
                $summary['total'],
                $data['coupon_id'] ?? null,
                $shippingAddress,
                $data['notes'] ?? null,
            ]);
            $orderId = (int)$this->db->lastInsertId();

            // Insert order items and reduce stock
            foreach ($summary['items'] as $item) {
                $this->db->prepare('INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([
                    $orderId, $item['product_id'], $item['name'], $item['sku'],
                    $item['quantity'], $item['price'], $item['price'] * $item['quantity']
                ]);
                $this->db->prepare('UPDATE products SET stock_quantity = stock_quantity - ?, sold_count = sold_count + ? WHERE id = ?')
                    ->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
            }

            // Record payment
            $this->db->prepare('INSERT INTO payments (order_id, payment_method, amount, status, transaction_id)
                VALUES (?, ?, ?, ?, ?)')->execute([
                $orderId,
                $data['payment_method'],
                $summary['total'],
                $data['payment_status'] ?? 'pending',
                $data['transaction_id'] ?? null,
            ]);

            // Update coupon usage
            if (!empty($data['coupon_id'])) {
                $this->db->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')->execute([$data['coupon_id']]);
            }

            $this->db->commit();
            $cart->clear();

            // Notify user
            if (!empty($data['user_id'])) {
                createNotification((int)$data['user_id'], 'order', 'Order Placed', "Your order #{$orderNumber} has been placed.", url('order-details.php?id=' . $orderId));
            }

            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber, 'total' => $summary['total']];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Order failed. Please try again.'];
        }
    }

    /**
     * Get order by ID with items
     */
    public function getById(int $orderId, ?int $userId = null): ?array
    {
        $sql = 'SELECT o.*, p.payment_method, p.status AS payment_status, p.transaction_id
            FROM orders o LEFT JOIN payments p ON p.order_id = o.id WHERE o.id = ?';
        $params = [$orderId];

        if ($userId) {
            $sql .= ' AND o.user_id = ?';
            $params[] = $userId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch();

        if (!$order) return null;

        $items = $this->db->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $items->execute([$orderId]);
        $order['items'] = $items->fetchAll();
        $order['shipping_address'] = json_decode($order['shipping_address'], true);

        return $order;
    }

    /**
     * Get user order history
     */
    public function getUserOrders(int $userId, int $page = 1): array
    {
        $count = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $count->execute([$userId]);
        $total = (int)$count->fetchColumn();
        $pagination = paginate($total, 10, $page);

        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$userId, $pagination['per_page'], $pagination['offset']]);

        return ['orders' => $stmt->fetchAll(), 'pagination' => $pagination];
    }

    /**
     * Update order status (admin)
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $valid = ['processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($status, $valid, true)) return false;

        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);

        // Notify customer
        $order = $this->getById($orderId);
        if ($order && $order['user_id']) {
            createNotification((int)$order['user_id'], 'order', 'Order Update', "Order #{$order['order_number']} is now: " . ucwords(str_replace('_', ' ', $status)), url('order-details.php?id=' . $orderId));
        }
        return true;
    }

    /**
     * Get order status timeline
     */
    public function getStatusTimeline(string $currentStatus): array
    {
        $steps = ['processing', 'packed', 'shipped', 'out_for_delivery', 'delivered'];
        $timeline = [];
        $reached = true;
        foreach ($steps as $step) {
            $timeline[] = ['status' => $step, 'completed' => $reached, 'current' => $step === $currentStatus];
            if ($step === $currentStatus) $reached = false;
        }
        if ($currentStatus === 'cancelled') {
            $timeline = [['status' => 'cancelled', 'completed' => true, 'current' => true]];
        }
        return $timeline;
    }
}
