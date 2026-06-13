<?php
/** MAB Shop - Notification Center */
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pdo = getDB();

if (isset($_GET['read'])) {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([(int)$_GET['read'], $_SESSION['user_id']]);
}
if (isset($_GET['read_all'])) {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$_SESSION['user_id']]);
    redirect(url('notifications.php'));
}

$notifications = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$notifications->execute([$_SESSION['user_id']]);

$pageTitle = 'Notifications';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4"><h1 class="fw-bold">Notifications</h1><a href="?read_all=1" class="btn btn-sm btn-outline-primary">Mark all read</a></div>
    <?php while ($n = $notifications->fetch()): ?>
    <div class="card border-0 shadow-sm mb-2 <?= !$n['is_read'] ? 'border-start border-primary border-3' : '' ?>">
        <div class="card-body d-flex justify-content-between">
            <div><strong><?= e($n['title']) ?></strong><p class="mb-0 text-muted small"><?= e($n['message']) ?></p><small class="text-muted"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></small></div>
            <?php if ($n['link']): ?><a href="<?= e($n['link']) ?>" class="btn btn-sm btn-outline-primary">View</a><?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
