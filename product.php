<?php

/**
 * MAB Shop - Product Detail Page
 * Image gallery with zoom, specs, reviews, related products
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Product.php';

$slug = sanitizeInput($_GET['slug'] ?? '');
if (!$slug) redirect(url('products.php'));

$productModel = new Product();
$product = $productModel->getBySlug($slug);
if (!$product) {
    setFlash('danger', 'Product not found.');
    redirect(url('products.php'));
}

$reviews = $productModel->getReviews((int)$product['id']);
$similar = $productModel->getRelated((int)$product['id'], 'similar', 4);
$boughtTogether = $productModel->getRelated((int)$product['id'], 'bought_together', 3);
$stock = getStockStatus((int)$product['stock_quantity'], (int)$product['low_stock_threshold']);

$pageTitle = $product['name'];
$metaDescription = $product['short_description'] ?? substr($product['description'], 0, 160);
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('products.php') ?>">Products</a></li>
            <?php if ($product['category_name']): ?>
                <li class="breadcrumb-item"><a href="<?= url('products.php?category=' . $product['category_id']) ?>"><?= e($product['category_name']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5 mb-5">
        <!-- Image Gallery -->
        <div class="col-lg-6">
            <div class="product-gallery">
                <?php $primaryImage = productImagePath($product['images'][0]['image_path'] ?? null, $product['slug']); ?>
                <div class="main-image-container mb-3">
                    <img src="<?= e($primaryImage) ?>" id="mainProductImage" alt="<?= e($product['name']) ?>">
                </div>
                <?php if (count($product['images']) > 1): ?>
                    <div class="thumbnail-list d-flex gap-2">
                        <?php foreach ($product['images'] as $i => $img): ?>
                            <img src="<?= e(productImagePath($img['image_path'] ?? null, $product['slug'])) ?>" alt="<?= e($img['alt_text'] ?? '') ?>" class="<?= $i === 0 ? 'active' : '' ?>"
                                onclick="document.getElementById('mainProductImage').src=this.src; document.querySelectorAll('.thumbnail-list img').forEach(i=>i.classList.remove('active')); this.classList.add('active');">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <?php if ($product['brand_name']): ?><span class="text-muted text-uppercase"><?= e($product['brand_name']) ?></span><?php endif; ?>
            <h1 class="fw-bold mb-2"><?= e($product['name']) ?></h1>
            <div class="d-flex align-items-center gap-3 mb-3">
                <?= renderStars((float)$product['rating_avg']) ?>
                <span class="text-muted">(<?= (int)$product['rating_count'] ?> reviews)</span>
                <span class="badge bg-<?= $stock['class'] ?>"><?= $stock['label'] ?></span>
            </div>
            <div class="mb-4">
                <span class="fs-2 fw-bold text-primary"><?= formatPrice((float)$product['price']) ?></span>
                <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                    <span class="text-muted text-decoration-line-through ms-2"><?= formatPrice((float)$product['compare_price']) ?></span>
                <?php endif; ?>
            </div>
            <p class="text-muted"><?= e($product['short_description'] ?? '') ?></p>

            <?php if ($product['color'] || $product['size']): ?>
                <div class="mb-3">
                    <?php if ($product['color']): ?><span class="me-3"><strong>Color:</strong> <?= e($product['color']) ?></span><?php endif; ?>
                    <?php if ($product['size']): ?><span><strong>Size:</strong> <?= e($product['size']) ?></span><?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="input-group" style="width: 140px;">
                    <button class="btn btn-outline-secondary" type="button" onclick="changeQty(-1)">-</button>
                    <input type="number" class="form-control text-center" id="productQty" value="1" min="1" max="<?= (int)$product['stock_quantity'] ?>">
                    <button class="btn btn-outline-secondary" type="button" onclick="changeQty(1)">+</button>
                </div>
                <button class="btn btn-primary btn-lg add-to-cart-btn flex-grow-1" data-id="<?= (int)$product['id'] ?>" data-quantity-input="productQty" id="addToCartBtn" <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <?php if (isLoggedIn()): ?>
                    <button class="btn btn-outline-danger wishlist-btn" data-id="<?= (int)$product['id'] ?>"><i class="bi bi-heart"></i></button>
                    <button class="btn btn-outline-secondary" onclick="addToCompare(<?= (int)$product['id'] ?>)"><i class="bi bi-arrow-left-right"></i></button>
                <?php endif; ?>
            </div>

            <!-- Social Share -->
            <div class="d-flex gap-2">
                <span class="text-muted small">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('product.php?slug=' . $product['slug'])) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-facebook"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url('product.php?slug=' . $product['slug'])) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-twitter-x"></i></a>
                <a href="https://wa.me/?text=<?= urlencode($product['name'] . ' ' . url('product.php?slug=' . $product['slug'])) ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Tabs: Description, Specs, Reviews -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Description</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">Specifications</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Reviews (<?= count($reviews) ?>)</button></li>
    </ul>
    <div class="tab-content mb-5">
        <div class="tab-pane fade show active" id="desc">
            <p><?= nl2br(e($product['description'])) ?></p>
        </div>
        <div class="tab-pane fade" id="specs">
            <?php if ($product['specifications']): ?>
                <table class="table">
                    <tbody>
                        <?php foreach ($product['specifications'] as $spec): ?>
                            <tr>
                                <th width="200"><?= e($spec['spec_key']) ?></th>
                                <td><?= e($spec['spec_value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?><p class="text-muted">No specifications available.</p><?php endif; ?>
        </div>
        <div class="tab-pane fade" id="reviews">
            <?php foreach ($reviews as $review): ?>
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <strong><?= e($review['first_name'] . ' ' . substr($review['last_name'], 0, 1) . '.') ?></strong>
                            <?= renderStars((float)$review['rating']) ?>
                        </div>
                        <?php if ($review['is_verified_buyer']): ?><span class="badge bg-success-subtle text-success"><i class="bi bi-patch-check"></i> Verified Buyer</span><?php endif; ?>
                        <p class="mt-2 mb-0"><?= e($review['comment']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (isLoggedIn()): ?>
                <form method="POST" action="<?= url('api/review.php') ?>" class="mt-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <h5>Write a Review</h5>
                    <select name="rating" class="form-select mb-2" style="width:120px" required>
                        <?php for ($r = 5; $r >= 1; $r--): ?><option value="<?= $r ?>"><?= $r ?> Stars</option><?php endfor; ?>
                    </select>
                    <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Your review..." required></textarea>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Frequently Bought Together -->
    <?php if ($boughtTogether): ?>
        <section class="mb-5">
            <h3 class="fw-bold mb-4">Frequently Bought Together</h3>
            <div class="row g-4">
                <?php foreach ($boughtTogether as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3"><?php $product = $p;
                                                            include ROOT_PATH . '/templates/product-card.php'; ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Similar Products -->
    <?php if ($similar): ?>
        <section class="mb-5">
            <h3 class="fw-bold mb-4">Similar Products</h3>
            <div class="row g-4">
                <?php foreach ($similar as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3"><?php $product = $p;
                                                            include ROOT_PATH . '/templates/product-card.php'; ?></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
    function changeQty(d) {
        const i = document.getElementById('productQty');
        i.value = Math.max(1, Math.min(<?= (int)$product['stock_quantity'] ?>, parseInt(i.value || '1') + d));
    }
    async function addToCompare(id) {
        try {
            const data = await apiPost('compare.php', {
                action: 'add',
                product_id: id
            });
            showToast(data.message || 'Compare list updated.', data.success ? 'success' : 'info');
        } catch (error) {
            showToast(error.message || 'Unable to update compare list.', 'danger');
        }
    }
</script>
<?php include ROOT_PATH . '/templates/footer.php'; ?>