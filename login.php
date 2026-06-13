<?php
/**
 * MAB Shop - User Login Page
 * Login with remember me and social login placeholders
 */
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) redirect(url('dashboard.php'));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $result = authenticateUser(
        sanitizeInput($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        !empty($_POST['remember'])
    );
    if ($result['success']) {
        setFlash('success', $result['message']);
        redirect(url($_GET['redirect'] ?? 'dashboard.php'));
    }
    $error = $result['message'];
}

$pageTitle = 'Login';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4">
                <h2 class="fw-bold text-center mb-4">Welcome Back</h2>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-field">
                            <input type="password" name="password" class="form-control" required autocomplete="current-password">
                            <button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="<?= url('forgot-password.php') ?>">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                </form>
                <div class="text-center text-muted mb-3">or continue with</div>
                <div class="d-flex gap-2 mb-3">
                    <a href="<?= url('auth/google.php') ?>" class="btn btn-outline-danger flex-fill"><i class="bi bi-google"></i> Google</a>
                    <a href="<?= url('auth/facebook.php') ?>" class="btn btn-outline-primary flex-fill"><i class="bi bi-facebook"></i> Facebook</a>
                </div>
                <p class="text-center mb-0">Don't have an account? <a href="<?= url('register.php') ?>">Register</a></p>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
