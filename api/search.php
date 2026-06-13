<?php
/**
 * MAB Shop - Search Autocomplete API
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Product.php';

header('Content-Type: application/json');

$query = sanitizeInput($_GET['q'] ?? '');
if (strlen($query) < 2) {
    jsonResponse(['suggestions' => []]);
}

$productModel = new Product();
$suggestions = $productModel->searchSuggestions($query);

foreach ($suggestions as &$s) {
    $s['price'] = formatPrice((float)$s['price']);
}

jsonResponse(['suggestions' => $suggestions]);
