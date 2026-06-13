<?php
/**
 * MAB Shop - Checkout Page
 * Guest checkout, address, payment method selection
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Cart.php';
require_once ROOT_PATH . '/classes/Order.php';

$cart = new Cart();
$summary = $cart->getSummary($_SESSION['coupon_id'] ?? null);
if (empty($summary['items'])) {
    setFlash('warning', 'Your cart is empty.');
    redirect(url('cart.php'));
}

$user = currentUser();
$addresses = [];
if ($user) {
    $stmt = getDB()->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC');
    $stmt->execute([$user['id']]);
    $addresses = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $shippingAddress = [
        'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'address_line1' => sanitizeInput($_POST['address_line1'] ?? ''),
        'address_line2' => sanitizeInput($_POST['address_line2'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'region' => sanitizeInput($_POST['region'] ?? ''),
        'country' => sanitizeInput($_POST['country'] ?? 'Ghana'),
    ];

    $orderModel = new Order();
    $result = $orderModel->create([
        'user_id' => $user ? (int)$user['id'] : null,
        'guest_email' => !$user ? sanitizeInput($_POST['guest_email'] ?? '') : null,
        'shipping_address' => $shippingAddress,
        'payment_method' => sanitizeInput($_POST['payment_method'] ?? 'mobile_money'),
        'payment_status' => 'completed',
        'transaction_id' => 'TXN' . strtoupper(uniqid()),
        'coupon_id' => $_SESSION['coupon_id'] ?? null,
        'notes' => sanitizeInput($_POST['notes'] ?? ''),
    ]);

    if ($result['success']) {
        unset($_SESSION['coupon_code'], $_SESSION['coupon_id']);
        setFlash('success', 'Order placed successfully! Order #' . $result['order_number']);
        redirect(url('order-details.php?id=' . $result['order_id']));
    }
    setFlash('danger', $result['message'] ?? 'Checkout failed.');
}

$pageTitle = 'Checkout';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4">Checkout</h1>
    <form method="POST">
        <?= csrfField() ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <?php if (!$user): ?>
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold mb-3">Guest Checkout</h5>
                    <input type="email" name="guest_email" class="form-control" placeholder="Email for order confirmation" required>
                    <small class="text-muted">Or <a href="<?= url('login.php?redirect=checkout.php') ?>">login</a> for faster checkout</small>
                </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold mb-3">Shipping Address</h5>
                    <?php if ($addresses): ?>
                    <select class="form-select mb-3" id="addressSelect" onchange="fillAddress(this)">
                        <option value="">Select saved address</option>
                        <?php foreach ($addresses as $addr): ?>
                        <option value='<?= e(json_encode($addr)) ?>'><?= e($addr['label'] . ' - ' . $addr['address_line1']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="full_name" class="form-control" placeholder="Full Name" required value="<?= e($user['first_name'] ?? '') ?> <?= e($user['last_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><input type="tel" name="phone" class="form-control" placeholder="Phone" required value="<?= e($user['phone'] ?? '') ?>"></div>
                        <div class="col-12"><input type="text" name="address_line1" class="form-control" placeholder="Address Line 1" required></div>
                        <div class="col-12"><input type="text" name="address_line2" class="form-control" placeholder="Address Line 2 (optional)"></div>
                        <div class="col-md-4"><input type="text" name="city" class="form-control" placeholder="City" required></div>
                        <div class="col-md-4"><input type="text" name="region" class="form-control" placeholder="Region"></div>
                        <div class="col-md-4"><input type="text" name="country" class="form-control" value="Ghana"></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3">Payment Method</h5>
                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="payment_method" value="mobile_money" id="momo" checked><label class="form-check-label" for="momo"><i class="bi bi-phone"></i> Mobile Money (MTN/Vodafone/AirtelTigo)</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="payment_method" value="visa" id="visa"><label class="form-check-label" for="visa"><i class="bi bi-credit-card"></i> Visa Card</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="payment_method" value="mastercard" id="mc"><label class="form-check-label" for="mc"><i class="bi bi-credit-card-2-front"></i> Mastercard</label></div>
                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="payment_method" value="paypal" id="paypal"><label class="form-check-label" for="paypal"><i class="bi bi-paypal"></i> PayPal (Ready)</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" id="bank"><label class="form-check-label" for="bank"><i class="bi bi-bank"></i> Bank Transfer</label></div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    <?php foreach ($summary['items'] as $item): ?>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span><?= e($item['name']) ?> x<?= $item['quantity'] ?></span>
                        <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= formatPrice($summary['subtotal']) ?></span></div>
                    <div class="d-flex justify-content-between"><span>Tax</span><span><?= formatPrice($summary['tax']) ?></span></div>
                    <div class="d-flex justify-content-between"><span>Shipping</span><span><?= $summary['shipping'] > 0 ? formatPrice($summary['shipping']) : 'FREE' ?></span></div>
                    <?php if ($summary['discount'] > 0): ?>
                    <div class="d-flex justify-content-between text-success"><span>Discount</span><span>-<?= formatPrice($summary['discount']) ?></span></div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-primary"><?= formatPrice($summary['total']) ?></span></div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg mt-3">Place Order</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>function fillAddress(sel) { if(!sel.value)return; const a=JSON.parse(sel.value); document.querySelector('[name=full_name]').value=a.full_name; document.querySelector('[name=phone]').value=a.phone; document.querySelector('[name=address_line1]').value=a.address_line1; document.querySelector('[name=city]').value=a.city; document.querySelector('[name=region]').value=a.region||''; }</script>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
