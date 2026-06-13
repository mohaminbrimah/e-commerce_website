<?php
/**
 * MAB Shop - Product Quick View API
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Product.php';

header('Content-Type: application/json');

$slug = sanitizeInput($_GET['slug'] ?? '');
$productModel = new Product();
$product = $productModel->getBySlug($slug);

if (!$product) {
    jsonResponse(['success' => false], 404);
}

$product['price_formatted'] = formatPrice((float)$product['price']);
$product['image'] = $product['images'][0]['image_path'] ?? null;

jsonResponse(['success' => true, 'product' => $product]);
