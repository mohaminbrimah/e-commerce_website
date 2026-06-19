<?php

/**
 * MAB Shop - Homepage
 * Featured products, categories, hero banner, and recommendations
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Product.php';

$productModel = new Product();
$featured = $productModel->getFeatured(8);
$recentlyViewed = $productModel->getRecentlyViewed(6);
$filterOptions = $productModel->getFilterOptions();

// Ensure featured products resolve to local assets in /assets/images
foreach ($featured as $i => $p) {
    // If product has no image or the image file referenced doesn't exist, try resolving via helper
    $candidate = $p['image'] ?? null;
    if (empty($candidate) || !file_exists(ROOT_PATH . '/' . ltrim((string)$candidate, '/'))) {
        $featured[$i]['image'] = productImagePath($candidate ?? null, $p['slug'] ?? null);
    }
}

$pageTitle = 'Home';
$metaDescription = 'MAB Shop - Premium online shopping in Ghana. Electronics, fashion, footwear and more.';
include ROOT_PATH . '/templates/header.php';
?>

<!-- Hero Section -->
<section class="hero-section mb-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 animate-fade-in">
                <span class="hero-kicker">Fresh arrivals are ready</span>
                <h1>Discover Quality Products at <?= APP_NAME ?></h1>
                <p class="lead mb-4 opacity-90">Shop the latest electronics, fashion, and footwear with secure payments and fast delivery across Ghana.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= url('products.php') ?>" class="btn btn-light btn-lg px-4"><i class="bi bi-bag-check me-2"></i>Shop Now</a>
                    <a href="<?= url('products.php?featured=1') ?>" class="btn btn-outline-light btn-lg"><i class="bi bi-stars me-2"></i>Featured Deals</a>
                </div>
                <div class="hero-proof d-flex flex-wrap gap-3 mt-4">
                    <span><i class="bi bi-truck"></i> Fast delivery</span>
                    <span><i class="bi bi-shield-check"></i> Secure checkout</span>
                    <span><i class="bi bi-headset"></i> Live support</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-product-grid" aria-label="Featured product images">
                    <img src="<?= url('assets/images/iphone14.jpg') ?>" alt="iPhone 14" class="hero-product hero-product-large">
                    <img src="<?= url('assets/images/nike_air_max90.jpg') ?>" alt="Nike Air Max 90" class="hero-product">
                    <img src="<?= url('assets/images/sony_wh-1000xm5.jpg') ?>" alt="Sony headphones" class="hero-product contain sony">
                    <img src="<?= url('assets/images/adidas_ultraboost22.jpg') ?>" alt="Adidas Ultraboost 22" class="hero-product contain adidas">
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- Categories -->
    <section class="mb-5">
        <h2 class="fw-bold mb-4">Shop by Category</h2>
        <div class="row g-3">
            <?php foreach (array_filter($filterOptions['categories'], fn($c) => !$c['parent_id']) as $cat): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?= url('products.php?category=' . $cat['id']) ?>" class="card text-center p-4 text-decoration-none h-100 border-0 shadow-sm category-card">
                        <i class="bi bi-grid fs-1 text-primary mb-2"></i>
                        <h6 class="mb-0 text-body"><?= e($cat['name']) ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Featured Products</h2>
            <a href="<?= url('products.php?featured=1') ?>" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <?php include ROOT_PATH . '/templates/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Recently Viewed -->
    <?php if (!empty($recentlyViewed)): ?>
        <section class="mb-5">
            <h2 class="fw-bold mb-4">Recently Viewed</h2>
            <div class="row g-4">
                <?php foreach ($recentlyViewed as $product): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <?php include ROOT_PATH . '/templates/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Trust badges -->
    <section class="mb-5">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="stat-card"><i class="bi bi-truck stat-icon text-primary"></i>
                    <h6 class="mt-2">Fast Delivery</h6>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card"><i class="bi bi-shield-check stat-icon text-success"></i>
                    <h6 class="mt-2">Secure Payments</h6>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card"><i class="bi bi-arrow-repeat stat-icon text-warning"></i>
                    <h6 class="mt-2">Easy Returns</h6>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card"><i class="bi bi-headset stat-icon text-info"></i>
                    <h6 class="mt-2">24/7 Support</h6>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>