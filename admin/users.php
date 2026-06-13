<?php
/**
 * MAB Shop - Admin User Management
 * View, suspend, and block users
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $userId = (int)$_POST['user_id'];
    $blocked = (int)($_POST['is_blocked'] ?? 0);
    $pdo->prepare('UPDATE users SET is_blocked = ? WHERE id = ? AND role != "admin"')->execute([$blocked, $userId]);
    setFlash('success', $blocked ? 'User blocked.' : 'User unblocked.');
    redirect(url('admin/users.php'));
}

$users = $pdo->query('SELECT id, email, first_name, last_name, phone, role, email_verified, is_blocked, last_login, created_at FROM users WHERE role = "customer" ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Users';
include dirname(__DIR__) . '/templates/admin/header.php';
?>

<h1 class="fw-bold mb-4">User Management</h1>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Verified</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['phone'] ?? '-') ?></td>
                <td><?= $u['email_verified'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' ?></td>
                <td><?= $u['is_blocked'] ? '<span class="badge bg-danger">Blocked</span>' : '<span class="badge bg-success">Active</span>' ?></td>
                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <form method="POST" class="d-inline"><?= csrfField() ?>
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="is_blocked" value="<?= $u['is_blocked'] ? 0 : 1 ?>">
                        <button class="btn btn-sm btn-outline-<?= $u['is_blocked'] ? 'success' : 'danger' ?>"><?= $u['is_blocked'] ? 'Unblock' : 'Block' ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include dirname(__DIR__) . '/templates/admin/footer.php'; ?>
