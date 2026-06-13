<?php
/**
 * MAB Shop - Product Comparison Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$pdo = getDB();
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$sessionId = $userId ? null : getGuestSessionId();

if ($userId) {
    $stmt = $pdo->prepare('SELECT p.*, pi.image_path AS image FROM compare_items ci JOIN products p ON ci.product_id = p.id LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE ci.user_id = ? LIMIT 4');
    $stmt->execute([$userId]);
} else {
    $stmt = $pdo->prepare('SELECT p.*, pi.image_path AS image FROM compare_items ci JOIN products p ON ci.product_id = p.id LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE ci.session_id = ? LIMIT 4');
    $stmt->execute([$sessionId]);
}
$products = $stmt->fetchAll();

// Get all specs for comparison
$allSpecs = [];
foreach ($products as $p) {
    $specs = $pdo->prepare('SELECT spec_key, spec_value FROM product_specifications WHERE product_id = ?');
    $specs->execute([$p['id']]);
    foreach ($specs->fetchAll() as $s) {
        $allSpecs[$s['spec_key']][$p['id']] = $s['spec_value'];
    }
}

$pageTitle = 'Compare Products';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4">Compare Products</h1>
    <?php if (count($products) < 2): ?>
    <p class="text-muted">Add at least 2 products to compare. <a href="<?= url('products.php') ?>">Browse products</a></p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table compare-table">
            <thead><tr><th>Feature</th><?php foreach ($products as $p): ?><th class="text-center"><img src="<?= url(productImagePath($p['image'] ?? null, $p['slug'] ?? null)) ?>" width="80" class="rounded mb-2"><br><?= e($p['name']) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
                <tr><td><strong>Price</strong></td><?php foreach ($products as $p): ?><td class="text-center fw-bold text-primary"><?= formatPrice((float)$p['price']) ?></td><?php endforeach; ?></tr>
                <tr><td><strong>Rating</strong></td><?php foreach ($products as $p): ?><td class="text-center"><?= renderStars((float)$p['rating_avg']) ?></td><?php endforeach; ?></tr>
                <tr><td><strong>Brand</strong></td><?php foreach ($products as $p): $bStmt = $pdo->prepare('SELECT name FROM brands WHERE id = ?'); $bStmt->execute([(int)($p['brand_id'] ?? 0)]); $b = $bStmt->fetch(); ?><td class="text-center"><?= e($b['name'] ?? 'N/A') ?></td><?php endforeach; ?></tr>
                <tr><td><strong>Stock</strong></td><?php foreach ($products as $p): $s = getStockStatus((int)$p['stock_quantity']); ?><td class="text-center"><span class="badge bg-<?= $s['class'] ?>"><?= $s['label'] ?></span></td><?php endforeach; ?></tr>
                <?php foreach ($allSpecs as $key => $values): ?>
                <tr><td><strong><?= e($key) ?></strong></td><?php foreach ($products as $p): ?><td class="text-center <?= count(array_unique($values)) > 1 && isset($values[$p['id']]) ? 'compare-highlight' : '' ?>"><?= e($values[$p['id']] ?? '-') ?></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
                <tr><td></td><?php foreach ($products as $p): ?><td class="text-center"><button class="btn btn-primary btn-sm add-to-cart-btn" data-id="<?= (int)$p['id'] ?>">Add to Cart</button></td><?php endforeach; ?></tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
