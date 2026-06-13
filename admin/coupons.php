<?php
/**
 * MAB Shop - Admin Coupon Management
 * Create and manage discount codes
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    if (($_POST['action'] ?? '') === 'add') {
        $pdo->prepare('INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_uses, expires_at) VALUES (?,?,?,?,?,?,?)')->execute([
            strtoupper(sanitizeInput($_POST['code'])),
            sanitizeInput($_POST['description'] ?? ''),
            $_POST['discount_type'],
            (float)$_POST['discount_value'],
            (float)$_POST['min_order_amount'],
            (int)($_POST['max_uses'] ?: null) ?: null,
            $_POST['expires_at'] ?: null,
        ]);
        setFlash('success', 'Coupon created.');
    } elseif (($_POST['action'] ?? '') === 'toggle') {
        $pdo->prepare('UPDATE coupons SET is_active = NOT is_active WHERE id = ?')->execute([(int)$_POST['id']]);
        setFlash('success', 'Coupon updated.');
    }
    redirect(url('admin/coupons.php'));
}

$coupons = $pdo->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Coupons';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-bold">Coupon Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal"><i class="bi bi-plus"></i> Create Coupon</button>
</div>

<div class="card border-0 shadow-sm">
    <table class="table table-hover mb-0">
        <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used</th><th>Expires</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($coupons as $c): ?>
        <tr>
            <td><strong><?= e($c['code']) ?></strong></td>
            <td><?= ucfirst($c['discount_type']) ?></td>
            <td><?= $c['discount_type'] === 'percentage' ? $c['discount_value'] . '%' : formatPrice((float)$c['discount_value']) ?></td>
            <td><?= formatPrice((float)$c['min_order_amount']) ?></td>
            <td><?= $c['used_count'] ?><?= $c['max_uses'] ? '/' . $c['max_uses'] : '' ?></td>
            <td><?= $c['expires_at'] ? date('M j, Y', strtotime($c['expires_at'])) : 'Never' ?></td>
            <td><span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td><form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-outline-secondary">Toggle</button></form></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="couponModal"><div class="modal-dialog"><form method="POST" class="modal-content"><?= csrfField() ?><input type="hidden" name="action" value="add">
    <div class="modal-header"><h5>Create Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label>Code</label><input type="text" name="code" class="form-control" required></div>
        <div class="mb-3"><label>Description</label><input type="text" name="description" class="form-control"></div>
        <div class="row"><div class="col-6 mb-3"><label>Type</label><select name="discount_type" class="form-select"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
        <div class="col-6 mb-3"><label>Value</label><input type="number" step="0.01" name="discount_value" class="form-control" required></div></div>
        <div class="row"><div class="col-6 mb-3"><label>Min Order</label><input type="number" step="0.01" name="min_order_amount" class="form-control" value="0"></div>
        <div class="col-6 mb-3"><label>Max Uses</label><input type="number" name="max_uses" class="form-control"></div></div>
        <div class="mb-3"><label>Expires At</label><input type="datetime-local" name="expires_at" class="form-control"></div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-primary">Create</button></div>
</form></div></div>
<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
