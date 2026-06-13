<?php
/** MAB Shop - Profile Management */
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$user = currentUser();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $pdo->prepare('UPDATE users SET first_name=?, last_name=?, phone=? WHERE id=?')->execute([
        sanitizeInput($_POST['first_name']), sanitizeInput($_POST['last_name']), sanitizeInput($_POST['phone']), $_SESSION['user_id']
    ]);
    setFlash('success', 'Profile updated.');
    redirect(url('profile.php'));
}

$pageTitle = 'Profile';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container py-4">
    <h1 class="fw-bold mb-4">Edit Profile</h1>
    <div class="card border-0 shadow-sm p-4 col-lg-6">
        <form method="POST"><?= csrfField() ?>
            <div class="mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control" value="<?= e($user['first_name']) ?>" required></div>
            <div class="mb-3"><label>Last Name</label><input type="text" name="last_name" class="form-control" value="<?= e($user['last_name']) ?>" required></div>
            <div class="mb-3"><label>Email</label><input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled></div>
            <div class="mb-3"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>"></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
