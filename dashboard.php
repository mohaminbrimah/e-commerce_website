<?php
/**
 * MAB Shop - Customer Dashboard
 * Overview of orders, wishlist, notifications, and account
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once ROOT_PATH . '/classes/Order.php';
require_once ROOT_PATH . '/classes/Wishlist.php';

requireLogin();
$user = currentUser();
$orderModel = new Order();
$orders = $orderModel->getUserOrders((int)$user['id'], 1);
$wishlist = new Wishlist();
$wishlistItems = $wishlist->getItems((int)$user['id']);
$notifCount = getUnreadNotificationCount((int)$user['id']);

$pageTitle = 'Dashboard';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-4">
    <h1 class="fw-bold mb-4">Hello, <?= e($user['first_name']) ?>!</h1>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value"><?= count($orders['orders']) ?></div><div class="text-muted">Recent Orders</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value"><?= count($wishlistItems) ?></div><div class="text-muted">Wishlist Items</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value"><?= $notifCount ?></div><div class="text-muted">Notifications</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value"><?= getCartCount() ?></div><div class="text-muted">Cart Items</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex justify-content-between mb-3"><h5 class="fw-bold mb-0">Recent Orders</h5><a href="<?= url('orders.php') ?>">View All</a></div>
                <?php if (empty($orders['orders'])): ?>
                <p class="text-muted">No orders yet. <a href="<?= url('products.php') ?>">Start shopping</a></p>
                <?php else: foreach (array_slice($orders['orders'], 0, 5) as $order): ?>
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div><strong>#<?= e($order['order_number']) ?></strong><br><small class="text-muted"><?= date('M j, Y', strtotime($order['created_at'])) ?></small></div>
                    <span class="badge bg-primary"><?= ucwords(str_replace('_', ' ', $order['status'])) ?></span>
                    <span class="fw-bold"><?= formatPrice((float)$order['total_amount']) ?></span>
                    <a href="<?= url('order-details.php?id=' . $order['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <div class="list-group list-group-flush">
                    <a href="<?= url('profile.php') ?>" class="list-group-item list-group-item-action"><i class="bi bi-person"></i> Edit Profile</a>
                    <a href="<?= url('addresses.php') ?>" class="list-group-item list-group-item-action"><i class="bi bi-geo-alt"></i> Saved Addresses</a>
                    <a href="<?= url('wishlist.php') ?>" class="list-group-item list-group-item-action"><i class="bi bi-heart"></i> Wishlist</a>
                    <a href="<?= url('notifications.php') ?>" class="list-group-item list-group-item-action"><i class="bi bi-bell"></i> Notifications</a>
                    <a href="<?= url('settings.php') ?>" class="list-group-item list-group-item-action"><i class="bi bi-gear"></i> Account Settings</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
