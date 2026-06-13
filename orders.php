<?php
/**
 * MAB Shop - Order History Page
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Order.php';

requireLogin();
$orderModel = new Order();
$page = max(1, (int)($_GET['page'] ?? 1));
$data = $orderModel->getUserOrders((int)$_SESSION['user_id'], $page);

$pageTitle = 'My Orders';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4">Order History</h1>
    <?php if (empty($data['orders'])): ?>
    <p class="text-muted">No orders yet.</p>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($data['orders'] as $order): ?>
                <tr>
                    <td><strong>#<?= e($order['order_number']) ?></strong></td>
                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td><span class="badge bg-primary"><?= ucwords(str_replace('_', ' ', $order['status'])) ?></span></td>
                    <td><?= formatPrice((float)$order['total_amount']) ?></td>
                    <td><a href="<?= url('order-details.php?id=' . $order['id']) ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
