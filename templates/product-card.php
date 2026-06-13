<?php
/**
 * MAB Shop - Product Card Partial
 * Reusable product card for grids, carousels, and recommendations
 * Expects $product array with id, name, slug, price, image, rating_avg, etc.
 */
$stock = getStockStatus((int)($product['stock_quantity'] ?? 0));
$discount = !empty($product['compare_price']) && $product['compare_price'] > $product['price']
    ? round((1 - $product['price'] / $product['compare_price']) * 100) : 0;
?>
<div class="product-card card h-100 border-0 shadow-sm animate-fade-in">
    <div class="product-image-wrapper">
        <?php if ($discount > 0): ?>
        <span class="badge bg-danger product-badge">-<?= $discount ?>%</span>
        <?php endif; ?>
        <a href="<?= url('product.php?slug=' . e($product['slug'])) ?>">
            <img src="<?= url(productImagePath($product['image'] ?? null, $product['slug'] ?? null)) ?>" 
                 class="card-img-top product-image" alt="<?= e($product['name']) ?>" loading="lazy">
        </a>
        <div class="product-actions">
            <button class="btn btn-sm btn-light quick-view-btn" data-slug="<?= e($product['slug']) ?>" title="Quick View">
                <i class="bi bi-eye"></i>
            </button>
            <?php if (isLoggedIn()): ?>
            <button class="btn btn-sm btn-light wishlist-btn" data-id="<?= (int)$product['id'] ?>" title="Add to Wishlist">
                <i class="bi bi-heart"></i>
            </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-primary add-to-cart-btn" data-id="<?= (int)$product['id'] ?>" title="Add to Cart">
                <i class="bi bi-cart-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body d-flex flex-column">
        <?php if (!empty($product['brand_name'])): ?>
        <small class="text-muted text-uppercase"><?= e($product['brand_name']) ?></small>
        <?php endif; ?>
        <h6 class="card-title mb-1">
            <a href="<?= url('product.php?slug=' . e($product['slug'])) ?>" class="text-decoration-none text-body"><?= e($product['name']) ?></a>
        </h6>
        <div class="mb-2"><?= renderStars((float)($product['rating_avg'] ?? 0)) ?></div>
        <div class="mt-auto d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-primary fs-5"><?= formatPrice((float)$product['price']) ?></span>
                <?php if ($discount > 0): ?>
                <small class="text-muted text-decoration-line-through ms-1"><?= formatPrice((float)$product['compare_price']) ?></small>
                <?php endif; ?>
            </div>
            <span class="badge bg-<?= $stock['class'] ?>-subtle text-<?= $stock['class'] ?>"><?= $stock['label'] ?></span>
        </div>
    </div>
</div>
