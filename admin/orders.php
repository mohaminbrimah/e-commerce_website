<?php
/**
 * MAB Shop - Admin Order Management
 * View orders, update status, process refunds
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Order.php';
requireAdmin();

$pdo = getDB();
$orderModel = new Order();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    if (($_POST['action'] ?? '') === 'update_status') {
        $orderModel->updateStatus((int)$_POST['order_id'], sanitizeInput($_POST['status']));
        if (!empty($_POST['tracking_number'])) {
            $pdo->prepare('UPDATE orders SET tracking_number = ? WHERE id = ?')->execute([sanitizeInput($_POST['tracking_number']), (int)$_POST['order_id']]);
        }
        setFlash('success', 'Order status updated.');
    } elseif (($_POST['action'] ?? '') === 'refund') {
        $orderId = (int)$_POST['order_id'];
        $pdo->prepare('UPDATE payments SET status = "refunded" WHERE order_id = ?')->execute([$orderId]);
        $orderModel->updateStatus($orderId, 'cancelled');
        setFlash('success', 'Refund processed.');
    }
    redirect(url('admin/orders.php'));
}

// Single order view
$viewOrder = null;
if (!empty($_GET['id'])) {
    $viewOrder = $orderModel->getById((int)$_GET['id']);
}

$orders = $pdo->query('SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 50')->fetchAll();
$statuses = ['processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];

$pageTitle = 'Orders';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<h1 class="fw-bold mb-4">Order Management</h1>

<?php if ($viewOrder): ?>
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between mb-3">
        <h5>Order #<?= e($viewOrder['order_number']) ?></h5>
        <a href="<?= url('invoice.php?id=' . $viewOrder['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Invoice</a>
    </div>
    <form method="POST" class="row g-3 mb-4"><?= csrfField() ?>
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <div class="col-md-4"><select name="status" class="form-select"><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $viewOrder['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><input type="text" name="tracking_number" class="form-control" placeholder="Tracking number" value="<?= e($viewOrder['tracking_number'] ?? '') ?>"></div>
        <div class="col-md-4"><button type="submit" class="btn btn-primary">Update Status</button></div>
    </form>
    <table class="table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th></tr></thead><tbody>
    <?php foreach ($viewOrder['items'] as $item): ?>
    <tr><td><?= e($item['product_name']) ?></td><td><?= $item['quantity'] ?></td><td><?= formatPrice((float)$item['total_price']) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <div class="text-end fw-bold">Total: <?= formatPrice((float)$viewOrder['total_amount']) ?></div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= e($o['order_number']) ?></td>
                <td><?= e($o['first_name'] ? $o['first_name'] . ' ' . $o['last_name'] : ($o['guest_email'] ?? 'Guest')) ?></td>
                <td><?= formatPrice((float)$o['total_amount']) ?></td>
                <td><span class="badge bg-primary"><?= ucwords(str_replace('_', ' ', $o['status'])) ?></span></td>
                <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td>
                    <a href="?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Process refund?')"><?= csrfField() ?><input type="hidden" name="action" value="refund"><input type="hidden" name="order_id" value="<?= $o['id'] ?>"><button class="btn btn-sm btn-outline-warning">Refund</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
