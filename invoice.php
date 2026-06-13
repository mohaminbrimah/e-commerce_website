<?php
/**
 * MAB Shop - Invoice / PDF Receipt Generation
 * Printable invoice page (use browser Print to PDF)
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Order.php';

$orderId = (int)($_GET['id'] ?? 0);
$orderModel = new Order();
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$isAdmin = isAdmin();
$order = $orderModel->getById($orderId, $isAdmin ? null : $userId);

if (!$order) die('Invoice not found.');
$addr = $order['shipping_address'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= e($order['order_number']) ?> | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{padding:2rem} @media print{.no-print{display:none}}</style>
</head>
<body>
    <div class="no-print mb-3"><button onclick="window.print()" class="btn btn-primary">Download PDF / Print</button></div>
    <div class="container">
        <div class="d-flex justify-content-between mb-4">
            <div><img src="<?= url('assets/images/mab-shop-logo.png') ?>" alt="<?= APP_NAME ?>" height="48" class="mb-2"><p class="text-muted mb-0">Accra, Ghana<br>support@mabshop.com</p></div>
            <div class="text-end"><h3>INVOICE</h3><p>#<?= e($order['order_number']) ?><br>Date: <?= date('M j, Y', strtotime($order['created_at'])) ?></p></div>
        </div>
        <div class="row mb-4">
            <div class="col-6"><strong>Bill To:</strong><br><?= e($addr['full_name'] ?? '') ?><br><?= e($addr['address_line1'] ?? '') ?><br><?= e($addr['city'] ?? '') ?>, <?= e($addr['country'] ?? '') ?><br><?= e($addr['phone'] ?? '') ?></div>
            <div class="col-6 text-end"><strong>Status:</strong> <?= ucwords(str_replace('_', ' ', $order['status'])) ?><br><strong>Payment:</strong> <?= ucfirst($order['payment_method'] ?? 'N/A') ?></div>
        </div>
        <table class="table table-bordered">
            <thead class="table-light"><tr><th>Item</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
            <tr><td><?= e($item['product_name']) ?></td><td><?= e($item['product_sku'] ?? '-') ?></td><td><?= $item['quantity'] ?></td><td><?= formatPrice((float)$item['unit_price']) ?></td><td><?= formatPrice((float)$item['total_price']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" class="text-end">Subtotal</td><td><?= formatPrice((float)$order['subtotal']) ?></td></tr>
                <tr><td colspan="4" class="text-end">Tax</td><td><?= formatPrice((float)$order['tax_amount']) ?></td></tr>
                <tr><td colspan="4" class="text-end">Shipping</td><td><?= formatPrice((float)$order['shipping_amount']) ?></td></tr>
                <?php if ($order['discount_amount'] > 0): ?><tr><td colspan="4" class="text-end text-success">Discount</td><td class="text-success">-<?= formatPrice((float)$order['discount_amount']) ?></td></tr><?php endif; ?>
                <tr class="fw-bold"><td colspan="4" class="text-end">Total</td><td><?= formatPrice((float)$order['total_amount']) ?></td></tr>
            </tfoot>
        </table>
        <p class="text-muted text-center mt-4">Thank you for shopping at <?= APP_NAME ?>!</p>
    </div>
</body>
</html>
