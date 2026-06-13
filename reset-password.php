<?php
/** MAB Shop - Reset Password Page */
require_once __DIR__ . '/includes/bootstrap.php';

$token = sanitizeInput($_GET['token'] ?? $_POST['token'] ?? '');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $result = resetPassword($token, $_POST['password'] ?? '');
    $message = $result['message'];
    if ($result['success']) { setFlash('success', $message); redirect(url('login.php')); }
}

$pageTitle = 'Reset Password';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container py-5"><div class="row justify-content-center"><div class="col-md-5">
    <div class="card border-0 shadow-sm p-4">
        <h2 class="fw-bold text-center mb-4">Set New Password</h2>
        <?php if ($message && !isset($result['success'])): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>
        <form method="POST"><?= csrfField() ?><input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label>New Password</label>
                <div class="password-field">
                    <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    <button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
</div></div></div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
