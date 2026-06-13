<?php
/**
 * MAB Shop - Newsletter Subscription API
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
requireCsrf();

$email = sanitizeInput($input['email'] ?? '');
if (!isValidEmail($email)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.']);
}

try {
    $pdo = getDB();
    $pdo->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?)')->execute([$email]);
    jsonResponse(['success' => true, 'message' => 'Subscribed successfully!']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Email already subscribed.']);
}
