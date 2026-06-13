<?php
/**
 * MAB Shop - User Settings API
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json');
requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
requireCsrf();

if (($input['action'] ?? '') === 'toggle_theme') {
    $theme = $input['theme'] === 'dark' ? 1 : 0;
    $pdo = getDB();
    $pdo->prepare('UPDATE users SET dark_mode = ? WHERE id = ?')->execute([$theme, $_SESSION['user_id']]);
    jsonResponse(['success' => true]);
}

jsonResponse(['success' => false]);
