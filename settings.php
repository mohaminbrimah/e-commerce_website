<?php
/** MAB Shop - Account Settings */
require_once __DIR__ . '/includes/bootstrap.php';
requireLogin();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    if (!empty($_POST['new_password'])) {
        $user = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $user->execute([$_SESSION['user_id']]);
        if (verifyPassword($_POST['current_password'] ?? '', $user->fetchColumn())) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([hashPassword($_POST['new_password']), $_SESSION['user_id']]);
            setFlash('success', 'Password changed.');
        } else {
            setFlash('danger', 'Current password incorrect.');
        }
    }
    $pdo->prepare('UPDATE users SET newsletter_subscribed = ?, preferred_language = ? WHERE id = ?')->execute([
        !empty($_POST['newsletter']) ? 1 : 0, sanitizeInput($_POST['language'] ?? 'en'), $_SESSION['user_id']
    ]);
    redirect(url('settings.php'));
}

$user = $pdo->prepare('SELECT newsletter_subscribed, preferred_language FROM users WHERE id = ?');
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

$pageTitle = 'Settings';
include ROOT_PATH . '/templates/header.php';
?>
<div class="container py-4">
    <h1 class="fw-bold mb-4">Account Settings</h1>
    <div class="card border-0 shadow-sm p-4 col-lg-6">
        <form method="POST"><?= csrfField() ?>
            <h5 class="mb-3">Change Password</h5>
            <div class="mb-3">
                <div class="password-field">
                    <input type="password" name="current_password" class="form-control" placeholder="Current Password" autocomplete="current-password">
                    <button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <div class="mb-3">
                <div class="password-field">
                    <input type="password" name="new_password" class="form-control" placeholder="New Password" minlength="8" autocomplete="new-password">
                    <button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye"></i></button>
                </div>
            </div>
            <hr>
            <h5 class="mb-3">Preferences</h5>
            <div class="form-check mb-3"><input type="checkbox" name="newsletter" class="form-check-input" id="nl" <?= $user['newsletter_subscribed'] ? 'checked' : '' ?>><label for="nl">Subscribe to newsletter</label></div>
            <div class="mb-3"><label>Language</label><select name="language" class="form-select"><option value="en" <?= $user['preferred_language'] === 'en' ? 'selected' : '' ?>>English</option><option value="tw" <?= $user['preferred_language'] === 'tw' ? 'selected' : '' ?>>Twi (Ready)</option><option value="fr" <?= $user['preferred_language'] === 'fr' ? 'selected' : '' ?>>French (Ready)</option></select></div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
