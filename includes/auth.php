<?php
/**
 * MAB Shop - Authentication
 * User registration, login, email verification, and password reset
 */

declare(strict_types=1);

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user data
 */
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, phone, avatar, role, dark_mode, preferred_language FROM users WHERE id = ? AND is_blocked = 0');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool
{
    $user = currentUser();
    return $user && in_array($user['role'], ['admin', 'moderator'], true);
}

/**
 * Require login or redirect
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Please log in to continue.');
        redirect(url('login.php'));
    }
}

/**
 * Require admin role
 */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied.');
    }
}

/**
 * Log in user and set session
 */
function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];

    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);
}

/**
 * Log out user
 */
function logoutUser(): void
{
    if (isLoggedIn()) {
        $pdo = getDB();
        $stmt = $pdo->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
    }
    clearRememberMe();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Register new user account
 */
function registerUser(string $email, string $password, string $firstName, string $lastName, ?string $phone = null): array
{
    if (!isValidEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    $token = generateToken();
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, phone, email_verification_token) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$email, hashPassword($password), $firstName, $lastName, $phone, $token]);

    // Send verification email (placeholder - configure SMTP in production)
    sendVerificationEmail($email, $firstName, $token);

    return ['success' => true, 'message' => 'Registration successful! Please check your email to verify your account.'];
}

/**
 * Authenticate user login
 */
function authenticateUser(string $email, string $password, bool $remember = false): array
{
    if (!checkRateLimit('login', 10, 300)) {
        return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !verifyPassword($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    if ($user['is_blocked']) {
        return ['success' => false, 'message' => 'Your account has been suspended. Contact support.'];
    }

    loginUser($user);
    if ($remember) {
        setRememberMe((int)$user['id']);
    }

    // Merge guest cart into user cart
    mergeGuestCart((int)$user['id']);

    return ['success' => true, 'message' => 'Welcome back, ' . $user['first_name'] . '!'];
}

/**
 * Verify email with token
 */
function verifyEmail(string $token): bool
{
    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE email_verification_token = ?');
    $stmt->execute([$token]);
    return $stmt->rowCount() > 0;
}

/**
 * Request password reset
 */
function requestPasswordReset(string $email): array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id, first_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Don't reveal if email exists
        return ['success' => true, 'message' => 'If that email exists, a reset link has been sent.'];
    }

    $token = generateToken();
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $stmt = $pdo->prepare('UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?');
    $stmt->execute([$token, $expires, $user['id']]);

    sendPasswordResetEmail($email, $user['first_name'], $token);

    return ['success' => true, 'message' => 'If that email exists, a reset link has been sent.'];
}

/**
 * Reset password with token
 */
function resetPassword(string $token, string $newPassword): array
{
    if (strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid or expired reset link.'];
    }

    $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?');
    $stmt->execute([hashPassword($newPassword), $user['id']]);

    return ['success' => true, 'message' => 'Password updated successfully. You can now log in.'];
}

/**
 * Send verification email (placeholder implementation)
 */
function sendVerificationEmail(string $email, string $name, string $token): void
{
    $link = url('verify-email.php?token=' . $token);
    $subject = APP_NAME . ' - Verify Your Email';
    $body = "Hello {$name},\n\nPlease verify your email: {$link}\n\nThank you,\n" . APP_NAME;
    @mail($email, $subject, $body, 'From: ' . MAIL_FROM);
}

/**
 * Send password reset email (placeholder implementation)
 */
function sendPasswordResetEmail(string $email, string $name, string $token): void
{
    $link = url('reset-password.php?token=' . $token);
    $subject = APP_NAME . ' - Password Reset';
    $body = "Hello {$name},\n\nReset your password: {$link}\n\nThis link expires in 1 hour.\n\n" . APP_NAME;
    @mail($email, $subject, $body, 'From: ' . MAIL_FROM);
}
