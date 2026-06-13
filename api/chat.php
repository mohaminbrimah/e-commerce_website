<?php
/**
 * MAB Shop - AI Shopping Assistant API
 * Natural language product search and recommendations
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Product.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$message = sanitizeInput($input['message'] ?? '');

if (!$message) {
    jsonResponse(['reply' => 'Please enter a message.']);
}

$pdo = getDB();
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$sessionId = getGuestSessionId();

// Save chat message
$stmt = $pdo->prepare('INSERT INTO chat_messages (user_id, session_id, sender, message) VALUES (?, ?, "user", ?)');
$stmt->execute([$userId, $sessionId, $message]);

$productModel = new Product();
$lower = strtolower($message);
$reply = '';
$products = [];

// Intent detection for natural language queries
if (preg_match('/hello|hi|hey/i', $message)) {
    $reply = "Hello! I'm the MAB Shop assistant. I can help you find products. Try asking: 'Show me black sneakers under GH₵300'";
} elseif (preg_match('/shipping|delivery/i', $message)) {
    $reply = "We offer free shipping on orders over " . formatPrice(FREE_SHIPPING_THRESHOLD) . ". Standard delivery takes 2-5 business days within Ghana.";
} elseif (preg_match('/return|refund/i', $message)) {
    $reply = "You can return unused items within 14 days of delivery for a full refund. Contact support@mabshop.com for assistance.";
} elseif (preg_match('/payment|pay|momo|mobile money/i', $message)) {
    $reply = "We accept Mobile Money (MTN, Vodafone, AirtelTigo), Visa, Mastercard, PayPal, and bank transfer.";
} elseif (preg_match('/show|find|search|sneaker|shoe|phone|laptop|black|under/i', $message)) {
    $result = $productModel->naturalLanguageSearch($message);
    $products = $result['products'] ?? [];
    if ($products) {
        $reply = "I found " . count($products) . " product(s) matching your request:";
        foreach ($products as &$p) {
            $p['price'] = formatPrice((float)$p['price']);
        }
    } else {
        $reply = "Sorry, I couldn't find products matching that. Try browsing our <a href='" . url('products.php') . "'>shop</a>.";
    }
} elseif (preg_match('/recommend|suggest/i', $message)) {
    $products = $productModel->getFeatured(4);
    $reply = "Here are some popular picks for you:";
    foreach ($products as &$p) {
        $p['price'] = formatPrice((float)$p['price']);
    }
} elseif (preg_match('/order|track/i', $message)) {
    if (isLoggedIn()) {
        $reply = "You can track your orders in your <a href='" . url('orders.php') . "'>order history</a>.";
    } else {
        $reply = "Please <a href='" . url('login.php') . "'>log in</a> to view your order status.";
    }
} else {
    $result = $productModel->getProducts(['search' => $message]);
    $products = array_slice($result['products'] ?? [], 0, 4);
    $reply = $products ? "Here's what I found:" : "I'm not sure about that. Try asking about products, shipping, or payments.";
    foreach ($products as &$p) {
        $p['price'] = formatPrice((float)$p['price']);
    }
}

// Save bot response
$stmt = $pdo->prepare('INSERT INTO chat_messages (user_id, session_id, sender, message) VALUES (?, ?, "bot", ?)');
$stmt->execute([$userId, $sessionId, strip_tags($reply)]);

jsonResponse(['reply' => $reply, 'products' => $products]);
