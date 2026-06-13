<?php
/**
 * MAB Shop - Order Details with tracking timeline and invoice
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Order.php';

$orderId = (int)($_GET['id'] ?? 0);
$orderModel = new Order();
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$order = $orderModel->getById($orderId, $userId);

if (!$order) {
    setFlash('danger', 'Order not found.');
    redirect(url('orders.php'));
}

$timeline = $orderModel->getStatusTimeline($order['status']);
$pageTitle = 'Order #' . $order['order_number'];
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('dashboard.php') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('orders.php') ?>">Orders</a></li>
            <li class="breadcrumb-item active">#<?= e($order['order_number']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Order #<?= e($order['order_number']) ?></h1>
        <a href="<?= url('invoice.php?id=' . $orderId) ?>" class="btn btn-outline-primary" target="_blank"><i class="bi bi-file-pdf"></i> Download Invoice</a>
    </div>

    <!-- Order Tracking Timeline -->
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-4">Order Tracking</h5>
        <div class="order-timeline">
            <?php foreach ($timeline as $step): ?>
            <div class="timeline-step <?= $step['completed'] ? 'completed' : '' ?> <?= $step['current'] ? 'current' : '' ?>">
                <div class="step-icon"><i class="bi bi-check-lg"></i></div>
                <small><?= ucwords(str_replace('_', ' ', $step['status'])) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($order['tracking_number']): ?>
        <p class="text-center mt-3">Tracking: <strong><?= e($order['tracking_number']) ?></strong></p>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Order Items</h5>
                <?php foreach ($order['items'] as $item): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?= e($item['product_name']) ?> x<?= $item['quantity'] ?></span>
                    <span><?= formatPrice((float)$item['total_price']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 mb-3">
                <h5 class="fw-bold mb-3">Summary</h5>
                <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= formatPrice((float)$order['subtotal']) ?></span></div>
                <div class="d-flex justify-content-between"><span>Tax</span><span><?= formatPrice((float)$order['tax_amount']) ?></span></div>
                <div class="d-flex justify-content-between"><span>Shipping</span><span><?= formatPrice((float)$order['shipping_amount']) ?></span></div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="d-flex justify-content-between text-success"><span>Discount</span><span>-<?= formatPrice((float)$order['discount_amount']) ?></span></div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><span><?= formatPrice((float)$order['total_amount']) ?></span></div>
            </div>
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Shipping Address</h5>
                <?php $addr = $order['shipping_address']; ?>
                <p class="mb-0"><?= e($addr['full_name'] ?? '') ?><br><?= e($addr['address_line1'] ?? '') ?><br><?= e($addr['city'] ?? '') ?>, <?= e($addr['country'] ?? '') ?><br><?= e($addr['phone'] ?? '') ?></p>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
