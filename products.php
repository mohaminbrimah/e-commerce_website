<?php
/**
 * MAB Shop - Product Listing Page
 * Search, filter, sort, and pagination
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Product.php';

$productModel = new Product();
$page = max(1, (int)($_GET['page'] ?? 1));
$sort = sanitizeInput($_GET['sort'] ?? 'newest');

// Build filters from query parameters
$filters = [];
if (!empty($_GET['category'])) $filters['category_id'] = (int)$_GET['category'];
if (!empty($_GET['brand'])) $filters['brand_id'] = (int)$_GET['brand'];
if (!empty($_GET['min_price'])) $filters['min_price'] = (float)$_GET['min_price'];
if (!empty($_GET['max_price'])) $filters['max_price'] = (float)$_GET['max_price'];
if (!empty($_GET['color'])) $filters['color'] = sanitizeInput($_GET['color']);
if (!empty($_GET['size'])) $filters['size'] = sanitizeInput($_GET['size']);
if (!empty($_GET['rating'])) $filters['min_rating'] = (float)$_GET['rating'];

// Natural language or standard search
if (!empty($_GET['q'])) {
    $query = sanitizeInput($_GET['q']);
    if (preg_match('/show me|under|sneaker|black|white/i', $query)) {
        $result = $productModel->naturalLanguageSearch($query);
    } else {
        $filters['search'] = $query;
        $result = $productModel->getProducts($filters, $sort, $page);
    }
} else {
    $result = $productModel->getProducts($filters, $sort, $page);
}

$products = $result['products'];
$pagination = $result['pagination'];
$filterOptions = $productModel->getFilterOptions();

$pageTitle = 'Products';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Home</a></li>
            <li class="breadcrumb-item active">Products</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm p-3">
                <h5 class="fw-bold mb-3">Filters</h5>
                <form method="GET" action="">
                    <?php if (!empty($_GET['q'])): ?><input type="hidden" name="q" value="<?= e($_GET['q']) ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($filterOptions['categories'] as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Brand</label>
                        <select name="brand" class="form-select form-select-sm">
                            <option value="">All Brands</option>
                            <?php foreach ($filterOptions['brands'] as $brand): ?>
                            <option value="<?= $brand['id'] ?>" <?= ($_GET['brand'] ?? '') == $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">Min Price</label><input type="number" name="min_price" class="form-control form-control-sm" value="<?= e($_GET['min_price'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Max Price</label><input type="number" name="max_price" class="form-control form-control-sm" value="<?= e($_GET['max_price'] ?? '') ?>"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <select name="color" class="form-select form-select-sm">
                            <option value="">Any</option>
                            <?php foreach ($filterOptions['colors'] as $color): ?>
                            <option value="<?= e($color) ?>" <?= ($_GET['color'] ?? '') === $color ? 'selected' : '' ?>><?= e($color) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Rating</label>
                        <select name="rating" class="form-select form-select-sm">
                            <option value="">Any</option>
                            <?php for ($r = 4; $r >= 1; $r--): ?>
                            <option value="<?= $r ?>" <?= ($_GET['rating'] ?? '') == $r ? 'selected' : '' ?>><?= $r ?>+ Stars</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                    <a href="<?= url('products.php') ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear</a>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted"><?= $pagination['total'] ?> products found</span>
                <select class="form-select form-select-sm w-auto" onchange="location.href=updateQuery('sort', this.value)">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                </select>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-search fs-1 text-muted"></i>
                <p class="mt-3">No products found. Try adjusting your filters.</p>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4">
                    <?php include ROOT_PATH . '/templates/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>function updateQuery(key, val) { const u = new URL(location); u.searchParams.set(key, val); return u; }</script>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
