<?php
/**
 * MAB Shop - Cart API Endpoint
 * AJAX handlers for add, update, remove cart items
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
requireCsrf();

$cart = new Cart();
$action = $input['action'] ?? '';

switch ($action) {
    case 'add':
        $result = $cart->add((int)($input['product_id'] ?? 0), (int)($input['quantity'] ?? 1));
        break;
    case 'update':
        $result = $cart->updateQuantity((int)($input['cart_item_id'] ?? 0), (int)($input['quantity'] ?? 1));
        break;
    case 'remove':
        $result = $cart->remove((int)($input['cart_item_id'] ?? 0));
        break;
    case 'save_later':
        $result = $cart->saveForLater((int)($input['cart_item_id'] ?? 0));
        break;
    case 'move_to_cart':
        $result = $cart->moveToCart((int)($input['cart_item_id'] ?? 0));
        break;
    case 'apply_coupon':
        $code = sanitizeInput($input['code'] ?? '');
        $summary = $cart->getSummary();
        $coupon = validateCoupon($code, $summary['subtotal']);
        if ($coupon) {
            $_SESSION['coupon_code'] = $code;
            $_SESSION['coupon_id'] = $coupon['id'];
            $totals = calculateOrderTotals($summary['subtotal'], $coupon['id']);
            $result = ['success' => true, 'message' => 'Coupon applied!', 'totals' => $totals];
        } else {
            $result = ['success' => false, 'message' => 'Invalid or expired coupon.'];
        }
        break;
    default:
        $result = ['success' => false, 'message' => 'Invalid action'];
}

jsonResponse($result);
