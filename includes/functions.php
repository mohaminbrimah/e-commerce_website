<?php

/**
 * MAB Shop - Helper Functions
 * URL generation, formatting, notifications, and common utilities
 */

declare(strict_types=1);

/**
 * Generate application URL
 */
function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirect to URL and exit
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Format price with currency symbol
 */
function formatPrice(float $amount): string
{
    return APP_CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Resolve product image paths from DB rows to real local assets.
 */
function productImagePath(?string $path, ?string $slug = null): string
{
    $slugMap = [
        'samsung-galaxy-a54' => 'assets/images/galaxyA54.jpg',
        'iphone-14' => 'assets/images/iphone14.jpg',
        'hp-pavilion-15' => 'assets/images/hp_pavillion15.jpg',
        'nike-air-max-90' => 'assets/images/nike_air_max90.jpg',
        'nike-revolution-6' => 'assets/images/nike_revolution6.jpg',
        'adidas-ultraboost-22' => 'assets/images/adidas_ultraboost22.jpg',
        'men-cotton-polo' => 'assets/images/cotton_polo_shirt.jpg',
        'sony-wh-1000xm5' => 'assets/images/sony_wh-1000xm5.jpg',
    ];

    $legacyMap = [
        'samsung-a54.jpg' => 'assets/images/galaxyA54.jpg',
        'iphone-14.jpg' => 'assets/images/iphone14.jpg',
        'hp-pavilion.jpg' => 'assets/images/hp_pavillion15.jpg',
        'nike-air-max.jpg' => 'assets/images/nike_air_max90.jpg',
        'nike-revolution.jpg' => 'assets/images/nike_revolution6.jpg',
        'adidas-ultraboost.jpg' => 'assets/images/adidas_ultraboost22.jpg',
        'polo-shirt.jpg' => 'assets/images/cotton_polo_shirt.jpg',
        'sony-headphones.jpg' => 'assets/images/sony_wh-1000xm5.jpg',
    ];

    $candidate = trim((string)$path);
    if ($candidate !== '' && file_exists(ROOT_PATH . '/' . ltrim($candidate, '/'))) {
        return $candidate;
    }

    $basename = basename(str_replace('\\', '/', $candidate));
    if ($basename && isset($legacyMap[$basename]) && file_exists(ROOT_PATH . '/' . $legacyMap[$basename])) {
        return $legacyMap[$basename];
    }

    if ($slug && isset($slugMap[$slug]) && file_exists(ROOT_PATH . '/' . $slugMap[$slug])) {
        return $slugMap[$slug];
    }

    return 'assets/images/mab-shop-logo.png';
}

/**
 * Create URL-friendly slug from string
 */
function createSlug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    return preg_replace('/-+/', '-', trim($text, '-'));
}

/**
 * Generate unique order number
 */
function generateOrderNumber(): string
{
    return 'MAB' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Create in-app notification for user
 */
function createNotification(int $userId, string $type, string $title, string $message, ?string $link = null): void
{
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $type, $title, $message, $link]);
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount(?int $userId = null): int
{
    if (!$userId && !isLoggedIn()) {
        return 0;
    }
    $userId = $userId ?? (int)$_SESSION['user_id'];
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get cart item count for current user/guest
 */
function getCartCount(): int
{
    $pdo = getDB();
    if (isLoggedIn()) {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ? AND saved_for_later = 0');
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE session_id = ? AND saved_for_later = 0');
        $stmt->execute([getGuestSessionId()]);
    }
    return (int)$stmt->fetchColumn();
}

/**
 * Merge guest cart into user cart on login
 */
function mergeGuestCart(int $userId): void
{
    $sessionId = $_SESSION['guest_id'] ?? null;
    if (!$sessionId) {
        return;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM cart_items WHERE session_id = ?');
    $stmt->execute([$sessionId]);
    $guestItems = $stmt->fetchAll();

    foreach ($guestItems as $item) {
        $check = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND saved_for_later = ?');
        $check->execute([$userId, $item['product_id'], $item['saved_for_later']]);
        $existing = $check->fetch();

        if ($existing) {
            $update = $pdo->prepare('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?');
            $update->execute([$item['quantity'], $existing['id']]);
        } else {
            $insert = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity, saved_for_later) VALUES (?, ?, ?, ?)');
            $insert->execute([$userId, $item['product_id'], $item['quantity'], $item['saved_for_later']]);
        }
    }

    $delete = $pdo->prepare('DELETE FROM cart_items WHERE session_id = ?');
    $delete->execute([$sessionId]);
}

/**
 * Track recently viewed product
 */
function trackRecentlyViewed(int $productId): void
{
    $pdo = getDB();
    $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
    $sessionId = $userId ? null : getGuestSessionId();

    if ($userId) {
        $pdo->prepare('DELETE FROM recently_viewed WHERE user_id = ? AND product_id = ?')->execute([$userId, $productId]);
        $pdo->prepare('INSERT INTO recently_viewed (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
    } else {
        $pdo->prepare('DELETE FROM recently_viewed WHERE session_id = ? AND product_id = ?')->execute([$sessionId, $productId]);
        $pdo->prepare('INSERT INTO recently_viewed (session_id, product_id) VALUES (?, ?)')->execute([$sessionId, $productId]);
    }
}

/**
 * Get stock availability label
 */
function getStockStatus(int $quantity, int $threshold = 5): array
{
    if ($quantity <= 0) {
        return ['label' => 'Out of Stock', 'class' => 'danger'];
    }
    if ($quantity <= $threshold) {
        return ['label' => 'Low Stock', 'class' => 'warning'];
    }
    return ['label' => 'In Stock', 'class' => 'success'];
}

/**
 * Calculate order totals
 */
function calculateOrderTotals(float $subtotal, ?int $couponId = null): array
{
    $discount = 0.0;
    if ($couponId) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM coupons WHERE id = ? AND is_active = 1');
        $stmt->execute([$couponId]);
        $coupon = $stmt->fetch();
        if ($coupon && $subtotal >= (float)$coupon['min_order_amount']) {
            if ($coupon['discount_type'] === 'percentage') {
                $discount = $subtotal * ((float)$coupon['discount_value'] / 100);
            } else {
                $discount = (float)$coupon['discount_value'];
            }
        }
    }

    $afterDiscount = max(0, $subtotal - $discount);
    $tax = $afterDiscount * TAX_RATE;
    $shipping = $afterDiscount >= FREE_SHIPPING_THRESHOLD ? 0 : STANDARD_SHIPPING_COST;
    $total = $afterDiscount + $tax + $shipping;

    return [
        'subtotal'   => round($subtotal, 2),
        'discount'   => round($discount, 2),
        'tax'        => round($tax, 2),
        'shipping'   => round($shipping, 2),
        'total'      => round($total, 2),
    ];
}

/**
 * Validate and apply coupon code
 */
function validateCoupon(string $code, float $subtotal): ?array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR used_count < max_uses)');
    $stmt->execute([strtoupper(trim($code))]);
    $coupon = $stmt->fetch();

    if (!$coupon || $subtotal < (float)$coupon['min_order_amount']) {
        return null;
    }
    return $coupon;
}

/**
 * Render star rating HTML
 */
function renderStars(float $rating): string
{
    $html = '<div class="star-rating" aria-label="Rating: ' . $rating . ' out of 5">';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= round($rating) ? 'bi-star-fill text-warning' : 'bi-star text-muted';
        $html .= '<i class="bi ' . $class . '"></i>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Paginate query results
 */
function paginate(int $total, int $perPage, int $currentPage): array
{
    $totalPages = max(1, (int)ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => $offset,
    ];
}

/**
 * Include template partial
 */
function partial(string $name, array $data = []): void
{
    extract($data);
    include ROOT_PATH . '/templates/' . $name . '.php';
}
