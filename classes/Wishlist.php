<?php
/**
 * MAB Shop - Wishlist Model
 * User-specific wishlist management
 */

declare(strict_types=1);

class Wishlist
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Get user's wishlist items
     */
    public function getItems(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT w.*, p.name, p.slug, p.price, p.stock_quantity, p.rating_avg,
            pi.image_path AS image
            FROM wishlist_items w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE w.user_id = ? ORDER BY w.created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Add product to wishlist
     */
    public function add(int $userId, int $productId): array
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)');
            $stmt->execute([$userId, $productId]);
            return ['success' => true, 'message' => 'Added to wishlist.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Already in wishlist.'];
        }
    }

    /**
     * Remove from wishlist
     */
    public function remove(int $userId, int $productId): array
    {
        $stmt = $this->db->prepare('DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        return ['success' => true, 'message' => 'Removed from wishlist.'];
    }

    /**
     * Move wishlist item to cart
     */
    public function moveToCart(int $userId, int $productId): array
    {
        $cart = new Cart();
        $result = $cart->add($productId);
        if ($result['success']) {
            $this->remove($userId, $productId);
        }
        return $result;
    }

    /**
     * Check if product is in wishlist
     */
    public function isInWishlist(int $userId, int $productId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM wishlist_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetch();
    }
}
