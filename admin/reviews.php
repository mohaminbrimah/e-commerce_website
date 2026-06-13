<?php
/**
 * MAB Shop - Admin Review Moderation
 * Approve or reject customer reviews
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $reviewId = (int)$_POST['review_id'];
    $status = sanitizeInput($_POST['status']);
    $pdo->prepare('UPDATE reviews SET status = ? WHERE id = ?')->execute([$status, $reviewId]);

    // Recalculate product rating if approved
    if ($status === 'approved') {
        $review = $pdo->prepare('SELECT product_id FROM reviews WHERE id = ?');
        $review->execute([$reviewId]);
        $productId = $review->fetchColumn();
        $stats = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ? AND status = "approved"');
        $stats->execute([$productId]);
        $s = $stats->fetch();
        $pdo->prepare('UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?')->execute([round($s['avg_rating'], 2), $s['cnt'], $productId]);
    }
    setFlash('success', 'Review ' . $status . '.');
    redirect(url('admin/reviews.php'));
}

$reviews = $pdo->query('SELECT r.*, u.first_name, u.last_name, p.name AS product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC')->fetchAll();

$pageTitle = 'Reviews';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<h1 class="fw-bold mb-4">Review Moderation</h1>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Product</th><th>User</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
            <tr>
                <td><?= e($r['product_name']) ?></td>
                <td><?= e($r['first_name']) ?><?= $r['is_verified_buyer'] ? ' <i class="bi bi-patch-check text-success"></i>' : '' ?></td>
                <td><?= renderStars((float)$r['rating']) ?></td>
                <td><small><?= e(substr($r['comment'], 0, 80)) ?>...</small></td>
                <td><span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= ucfirst($r['status']) ?></span></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                    <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Approve</button></form>
                    <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-danger">Reject</button></form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
