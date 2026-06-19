<?php

/**
 * MAB Shop - Shopping Cart Page
 * Cart items, save for later, summary with tax/shipping
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Cart.php';

$cart = new Cart();
$couponCode = $_SESSION['coupon_code'] ?? null;
$couponId = null;
if ($couponCode) {
    $coupon = validateCoupon($couponCode, 0);
    $couponId = $coupon['id'] ?? null;
}
$summary = $cart->getSummary($couponId);
$savedItems = $cart->getItems(true);

$pageTitle = 'Shopping Cart';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4">Shopping Cart</h1>

    <?php if (empty($summary['items']) && empty($savedItems)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x fs-1 text-muted"></i>
            <p class="mt-3">Your cart is empty.</p>
            <a href="<?= url('products.php') ?>" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <?php foreach ($summary['items'] as $item): ?>
                    <div class="card border-0 shadow-sm mb-3 cart-item" data-id="<?= (int)$item['id'] ?>">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <img src="<?= e(productImagePath($item['image'] ?? null, $item['slug'] ?? null)) ?>" width="80" height="80" class="rounded object-fit-cover" alt="<?= e($item['name']) ?>">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><a href="<?= url('product.php?slug=' . e($item['slug'])) ?>" class="text-decoration-none"><?= e($item['name']) ?></a></h6>
                                <span class="text-primary fw-bold"><?= formatPrice((float)$item['price']) ?></span>
                            </div>
                            <div class="input-group" style="width:120px">
                                <button class="btn btn-outline-secondary btn-sm qty-btn" data-delta="-1">-</button>
                                <input type="number" class="form-control form-control-sm text-center qty-input" value="<?= (int)$item['quantity'] ?>" min="1">
                                <button class="btn btn-outline-secondary btn-sm qty-btn" data-delta="1">+</button>
                            </div>
                            <strong class="item-total"><?= formatPrice($item['price'] * $item['quantity']) ?></strong>
                            <div class="d-flex flex-column gap-1">
                                <button class="btn btn-sm btn-outline-secondary save-later-btn" title="Save for later"><i class="bi bi-bookmark"></i></button>
                                <button class="btn btn-sm btn-outline-danger remove-btn" title="Remove"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($savedItems): ?>
                    <h5 class="mt-4 mb-3">Saved for Later</h5>
                    <?php foreach ($savedItems as $item): ?>
                        <div class="card border-0 shadow-sm mb-2 opacity-75 saved-item" data-id="<?= (int)$item['id'] ?>">
                            <div class="card-body d-flex gap-3 align-items-center py-2">
                                <img src="<?= e(productImagePath($item['image'] ?? null, $item['slug'] ?? null)) ?>" width="60" height="60" class="rounded object-fit-cover" alt="<?= e($item['name']) ?>">
                                <span class="flex-grow-1"><?= e($item['name']) ?></span>
                                <button class="btn btn-sm btn-primary move-cart-btn">Move to Cart</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 sticky-top" style="top:100px">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span id="subtotal"><?= formatPrice($summary['subtotal']) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Discount</span><span id="discount">-<?= formatPrice($summary['discount']) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Tax (12.5%)</span><span id="tax"><?= formatPrice($summary['tax']) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span id="shipping"><?= $summary['shipping'] > 0 ? formatPrice($summary['shipping']) : 'FREE' ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3"><span>Total</span><span id="total" class="text-primary"><?= formatPrice($summary['total']) ?></span></div>

                    <form id="couponForm" class="mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" name="code" class="form-control" placeholder="Coupon code" value="<?= e($couponCode ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">Apply</button>
                        </div>
                    </form>

                    <a href="<?= url('checkout.php') ?>" class="btn btn-primary w-100 btn-lg">Proceed to Checkout</a>
                    <a href="<?= url('products.php') ?>" class="btn btn-link w-100 mt-2">Continue Shopping</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= url('assets/js/cart.js') ?>"></script>
<?php include ROOT_PATH . '/templates/footer.php'; ?>