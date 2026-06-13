<?php
/**
 * MAB Shop - Product Review Submission
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $pdo = getDB();
    $productId = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = sanitizeInput($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        setFlash('danger', 'Invalid rating.');
        redirect(url('product.php?slug=' . ($_GET['slug'] ?? '')));
    }

    // Check if verified buyer
    $verified = $pdo->prepare('SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status = "delivered" LIMIT 1');
    $verified->execute([$_SESSION['user_id'], $productId]);

    $stmt = $pdo->prepare('INSERT INTO reviews (product_id, user_id, rating, comment, is_verified_buyer, status) VALUES (?, ?, ?, ?, ?, "pending")');
    $stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment, $verified->fetch() ? 1 : 0]);

    setFlash('success', 'Review submitted for moderation.');
    $slug = $pdo->prepare('SELECT slug FROM products WHERE id = ?');
    $slug->execute([$productId]);
    redirect(url('product.php?slug=' . $slug->fetchColumn()));
}
