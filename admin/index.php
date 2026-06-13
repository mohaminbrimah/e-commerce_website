<?php
/**
 * MAB Shop - Admin Dashboard
 * Analytics overview: users, orders, revenue, best sellers
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

// Dashboard statistics
$stats = [
    'users'  => (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn(),
    'orders' => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'revenue'=> (float)$pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != "cancelled"')->fetchColumn(),
    'products' => (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
];

// Monthly revenue (last 6 months)
$monthlyRevenue = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS revenue, COUNT(*) AS orders FROM orders WHERE status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month")->fetchAll();

// Best selling products
$bestSellers = $pdo->query('SELECT p.name, p.sold_count, p.price, pi.image_path AS image FROM products p LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 ORDER BY p.sold_count DESC LIMIT 5')->fetchAll();

// Recent orders
$recentOrders = $pdo->query('SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10')->fetchAll();

$pageTitle = 'Admin Dashboard';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<h1 class="fw-bold mb-4">Dashboard</h1>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="stat-card"><div class="d-flex justify-content-between"><div><div class="text-muted small">Total Users</div><div class="stat-value"><?= number_format($stats['users']) ?></div></div><i class="bi bi-people stat-icon text-primary"></i></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="d-flex justify-content-between"><div><div class="text-muted small">Total Orders</div><div class="stat-value"><?= number_format($stats['orders']) ?></div></div><i class="bi bi-box stat-icon text-success"></i></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="d-flex justify-content-between"><div><div class="text-muted small">Revenue</div><div class="stat-value"><?= formatPrice($stats['revenue']) ?></div></div><i class="bi bi-currency-exchange stat-icon text-warning"></i></div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="d-flex justify-content-between"><div><div class="text-muted small">Products</div><div class="stat-value"><?= number_format($stats['products']) ?></div></div><i class="bi bi-grid stat-icon text-info"></i></div></div></div>
</div>

<div class="row g-4">
    <!-- Monthly Reports -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3">Monthly Revenue</h5>
            <table class="table table-sm">
                <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php foreach ($monthlyRevenue as $row): ?>
                <tr><td><?= e($row['month']) ?></td><td><?= $row['orders'] ?></td><td><?= formatPrice((float)$row['revenue']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($monthlyRevenue)): ?><tr><td colspan="3" class="text-muted">No data yet</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Best Sellers -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3">Best Selling Products</h5>
            <?php foreach ($bestSellers as $p): ?>
            <div class="d-flex align-items-center gap-3 mb-2">
                <img src="<?= url($p['image'] ?? 'assets/images/placeholder.svg') ?>" width="40" height="40" class="rounded">
                <div class="flex-grow-1"><small><?= e($p['name']) ?></small></div>
                <span class="badge bg-primary"><?= (int)$p['sold_count'] ?> sold</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card border-0 shadow-sm p-4 mt-4">
    <h5 class="fw-bold mb-3">Recent Orders</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td>#<?= e($order['order_number']) ?></td>
                <td><?= e(($order['first_name'] ?? 'Guest') . ' ' . ($order['last_name'] ?? '')) ?></td>
                <td><?= formatPrice((float)$order['total_amount']) ?></td>
                <td><span class="badge bg-primary"><?= ucwords(str_replace('_', ' ', $order['status'])) ?></span></td>
                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                <td><a href="<?= url('admin/orders.php?id=' . $order['id']) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
