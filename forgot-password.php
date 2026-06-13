<?php
/**
 * MAB Shop - Forgot Password Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $result = requestPasswordReset(sanitizeInput($_POST['email'] ?? ''));
    $message = $result['message'];
}

$pageTitle = 'Forgot Password';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center"><div class="col-md-5">
        <div class="card border-0 shadow-sm p-4">
            <h2 class="fw-bold text-center mb-4">Reset Password</h2>
            <?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
            <form method="POST"><?= csrfField() ?>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            </form>
            <p class="text-center mt-3"><a href="<?= url('login.php') ?>">Back to Login</a></p>
        </div>
    </div></div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
