<?php
/**
 * MAB Shop - Cart Model
 * Shopping cart operations with session and database persistence
 */

declare(strict_types=1);

class Cart
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Get cart identifier (user ID or guest session)
     */
    private function getCartContext(): array
    {
        if (isLoggedIn()) {
            return ['user_id' => (int)$_SESSION['user_id'], 'session_id' => null];
        }
        return ['user_id' => null, 'session_id' => getGuestSessionId()];
    }

    /**
     * Build WHERE clause for cart queries
     */
    private function cartWhere(bool $savedForLater = false): array
    {
        $ctx = $this->getCartContext();
        if ($ctx['user_id']) {
            return ['user_id = ? AND saved_for_later = ?', [$ctx['user_id'], (int)$savedForLater]];
        }
        return ['session_id = ? AND saved_for_later = ?', [$ctx['session_id'], (int)$savedForLater]];
    }

    /**
     * Get all cart items with product details
     */
    public function getItems(bool $savedForLater = false): array
    {
        [$where, $params] = $this->cartWhere($savedForLater);
        $sql = "SELECT ci.*, p.name, p.slug, p.price, p.stock_quantity, p.sku,
                pi.image_path AS image
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE {$where}
                ORDER BY ci.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Add product to cart
     */
    public function add(int $productId, int $quantity = 1): array
    {
        $product = $this->db->prepare('SELECT id, stock_quantity, name FROM products WHERE id = ? AND is_active = 1');
        $product->execute([$productId]);
        $p = $product->fetch();

        if (!$p) {
            return ['success' => false, 'message' => 'Product not found.'];
        }
        if ($p['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock.'];
        }

        $ctx = $this->getCartContext();
        if ($ctx['user_id']) {
            $check = $this->db->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND saved_for_later = 0');
            $check->execute([$ctx['user_id'], $productId]);
        } else {
            $check = $this->db->prepare('SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ? AND saved_for_later = 0');
            $check->execute([$ctx['session_id'], $productId]);
        }
        $existing = $check->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            if ($newQty > $p['stock_quantity']) {
                return ['success' => false, 'message' => 'Cannot add more than available stock.'];
            }
            $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$newQty, $existing['id']]);
        } else {
            if ($ctx['user_id']) {
                $this->db->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)')->execute([$ctx['user_id'], $productId, $quantity]);
            } else {
                $this->db->prepare('INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)')->execute([$ctx['session_id'], $productId, $quantity]);
            }
        }

        return ['success' => true, 'message' => 'Added to cart.', 'count' => getCartCount()];
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(int $cartItemId, int $quantity): array
    {
        if ($quantity < 1) {
            return $this->remove($cartItemId);
        }

        [$where, $params] = $this->cartWhere();
        $stmt = $this->db->prepare("SELECT ci.*, p.stock_quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = ? AND {$where}");
        $stmt->execute(array_merge([$cartItemId], $params));
        $item = $stmt->fetch();

        if (!$item) {
            return ['success' => false, 'message' => 'Cart item not found.'];
        }
        if ($quantity > $item['stock_quantity']) {
            return ['success' => false, 'message' => 'Insufficient stock.'];
        }

        $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$quantity, $cartItemId]);
        return ['success' => true, 'message' => 'Cart updated.', 'count' => getCartCount()];
    }

    /**
     * Remove item from cart
     */
    public function remove(int $cartItemId): array
    {
        [$where, $params] = $this->cartWhere();
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = ? AND {$where}");
        $stmt->execute(array_merge([$cartItemId], $params));
        return ['success' => true, 'message' => 'Item removed.', 'count' => getCartCount()];
    }

    /**
     * Move item to save for later
     */
    public function saveForLater(int $cartItemId): array
    {
        [$where, $params] = $this->cartWhere();
        $stmt = $this->db->prepare("UPDATE cart_items SET saved_for_later = 1 WHERE id = ? AND {$where}");
        $stmt->execute(array_merge([$cartItemId], $params));
        return ['success' => true, 'message' => 'Saved for later.'];
    }

    /**
     * Move saved item back to cart
     */
    public function moveToCart(int $cartItemId): array
    {
        [$where] = $this->cartWhere(true);
        $ctx = $this->getCartContext();
        $params = $ctx['user_id'] ? [$ctx['user_id'], 1] : [$ctx['session_id'], 1];
        $stmt = $this->db->prepare("UPDATE cart_items SET saved_for_later = 0 WHERE id = ? AND {$where}");
        $stmt->execute(array_merge([$cartItemId], $params));
        return ['success' => true, 'message' => 'Moved to cart.'];
    }

    /**
     * Get cart summary with totals
     */
    public function getSummary(?int $couponId = null): array
    {
        $items = $this->getItems();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $totals = calculateOrderTotals($subtotal, $couponId);
        $totals['items'] = $items;
        $totals['item_count'] = count($items);
        return $totals;
    }

    /**
     * Clear cart after order
     */
    public function clear(): void
    {
        $ctx = $this->getCartContext();
        if ($ctx['user_id']) {
            $this->db->prepare('DELETE FROM cart_items WHERE user_id = ? AND saved_for_later = 0')->execute([$ctx['user_id']]);
        } else {
            $this->db->prepare('DELETE FROM cart_items WHERE session_id = ? AND saved_for_later = 0')->execute([$ctx['session_id']]);
        }
    }
}
