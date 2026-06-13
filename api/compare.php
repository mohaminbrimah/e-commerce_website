<?php
/**
 * MAB Shop - Product Compare API
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
requireCsrf();

$pdo = getDB();
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$sessionId = $userId ? null : getGuestSessionId();
$productId = (int)($input['product_id'] ?? 0);

// Check compare count (max 4)
if ($userId) {
    $count = $pdo->prepare('SELECT COUNT(*) FROM compare_items WHERE user_id = ?');
    $count->execute([$userId]);
} else {
    $count = $pdo->prepare('SELECT COUNT(*) FROM compare_items WHERE session_id = ?');
    $count->execute([$sessionId]);
}

if ((int)$count->fetchColumn() >= 4) {
    jsonResponse(['success' => false, 'message' => 'Maximum 4 products for comparison.']);
}

try {
    if ($userId) {
        $pdo->prepare('INSERT INTO compare_items (user_id, product_id) VALUES (?, ?)')->execute([$userId, $productId]);
    } else {
        $pdo->prepare('INSERT INTO compare_items (session_id, product_id) VALUES (?, ?)')->execute([$sessionId, $productId]);
    }
    jsonResponse(['success' => true, 'message' => 'Added to compare list.']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Already in compare list.']);
}
