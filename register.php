<?php
/**
 * MAB Shop - User Registration Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) redirect(url('dashboard.php'));

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $result = registerUser(
        sanitizeInput($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        sanitizeInput($_POST['first_name'] ?? ''),
        sanitizeInput($_POST['last_name'] ?? ''),
        sanitizeInput($_POST['phone'] ?? '')
    );
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Register';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <h2 class="fw-bold text-center mb-4">Create Account</h2>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
                <?php if (!$success): ?>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+233...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-field">
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                            <button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <?php endif; ?>
                <p class="text-center mt-3 mb-0">Already have an account? <a href="<?= url('login.php') ?>">Login</a></p>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
