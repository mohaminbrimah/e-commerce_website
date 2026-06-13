<?php
/**
 * MAB Shop - Wishlist API Endpoint
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Wishlist.php';

header('Content-Type: application/json');
requireLogin();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
requireCsrf();

$wishlist = new Wishlist();
$userId = (int)$_SESSION['user_id'];
$productId = (int)($input['product_id'] ?? 0);

$result = match ($input['action'] ?? '') {
    'add'    => $wishlist->add($userId, $productId),
    'remove' => $wishlist->remove($userId, $productId),
    'move_to_cart' => $wishlist->moveToCart($userId, $productId),
    default  => ['success' => false, 'message' => 'Invalid action'],
};

jsonResponse($result);
