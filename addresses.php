<?php
/** MAB Shop - Saved Addresses Management */
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    if (($_POST['action'] ?? '') === 'add') {
        if (!empty($_POST['is_default'])) {
            $pdo->prepare('UPDATE addresses SET is_default = 0 WHERE user_id = ?')->execute([$_SESSION['user_id']]);
        }
        $pdo->prepare('INSERT INTO addresses (user_id, label, full_name, phone, address_line1, address_line2, city, region, country, is_default) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([
            $_SESSION['user_id'], sanitizeInput($_POST['label']), sanitizeInput($_POST['full_name']), sanitizeInput($_POST['phone']),
            sanitizeInput($_POST['address_line1']), sanitizeInput($_POST['address_line2'] ?? ''), sanitizeInput($_POST['city']),
            sanitizeInput($_POST['region'] ?? ''), sanitizeInput($_POST['country'] ?? 'Ghana'), !empty($_POST['is_default']) ? 1 : 0
        ]);
        setFlash('success', 'Address added.');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM addresses WHERE id = ? AND user_id = ?')->execute([(int)$_POST['id'], $_SESSION['user_id']]);
        setFlash('success', 'Address deleted.');
    }
    redirect(url('addresses.php'));
}

$addresses = $pdo->prepare('SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC');
$addresses->execute([$_SESSION['user_id']]);
$addresses = $addresses->fetchAll();

$pageTitle = 'Addresses';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container py-4">
    <h1 class="fw-bold mb-4">Saved Addresses</h1>
    <div class="row g-4">
        <?php foreach ($addresses as $addr): ?>
        <div class="col-md-6"><div class="card border-0 shadow-sm p-3">
            <?php if ($addr['is_default']): ?><span class="badge bg-primary mb-2">Default</span><?php endif; ?>
            <strong><?= e($addr['label']) ?></strong>
            <p class="mb-2"><?= e($addr['full_name']) ?><br><?= e($addr['address_line1']) ?><br><?= e($addr['city']) ?>, <?= e($addr['country']) ?><br><?= e($addr['phone']) ?></p>
            <form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $addr['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
        </div></div>
        <?php endforeach; ?>
        <div class="col-md-6"><div class="card border-0 shadow-sm p-4">
            <h5>Add New Address</h5>
            <form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="add">
                <input type="text" name="label" class="form-control mb-2" placeholder="Label (Home, Work)" required>
                <input type="text" name="full_name" class="form-control mb-2" placeholder="Full Name" required>
                <input type="tel" name="phone" class="form-control mb-2" placeholder="Phone" required>
                <input type="text" name="address_line1" class="form-control mb-2" placeholder="Address" required>
                <input type="text" name="city" class="form-control mb-2" placeholder="City" required>
                <input type="text" name="region" class="form-control mb-2" placeholder="Region">
                <div class="form-check mb-2"><input type="checkbox" name="is_default" class="form-check-input" id="def"><label for="def">Set as default</label></div>
                <button type="submit" class="btn btn-primary">Add Address</button>
            </form>
        </div></div>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
