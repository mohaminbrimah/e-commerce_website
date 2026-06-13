<?php
/**
 * MAB Shop - Wishlist Page
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Wishlist.php';

requireLogin();
$wishlist = new Wishlist();
$items = $wishlist->getItems((int)$_SESSION['user_id']);

$pageTitle = 'Wishlist';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4">My Wishlist</h1>
    <?php if (empty($items)): ?>
    <p class="text-muted">Your wishlist is empty. <a href="<?= url('products.php') ?>">Browse products</a></p>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($items as $item): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card card h-100 border-0 shadow-sm">
                <img src="<?= url(productImagePath($item['image'] ?? null, $item['slug'] ?? null)) ?>" class="card-img-top product-image" alt="<?= e($item['name']) ?>" loading="lazy">
                <div class="card-body">
                    <h6><?= e($item['name']) ?></h6>
                    <span class="text-primary fw-bold"><?= formatPrice((float)$item['price']) ?></span>
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm btn-primary flex-fill add-to-cart-btn" data-id="<?= (int)$item['product_id'] ?>">Add to Cart</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeWishlist(<?= (int)$item['product_id'] ?>)"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script>
async function removeWishlist(id) {
    await fetch('<?= url('api/wishlist.php') ?>', {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({action:'remove',product_id:id})});
    location.reload();
}
</script>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
