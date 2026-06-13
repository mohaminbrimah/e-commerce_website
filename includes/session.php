<?php
/**
 * MAB Shop - Session Management
 * Secure session handling with remember-me support
 */

declare(strict_types=1);

/**
 * Start a secure PHP session with hardened cookie settings
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }

    // Check remember-me cookie for auto-login
    checkRememberMe();
}

/**
 * Get or create guest session identifier for cart/wishlist
 */
function getGuestSessionId(): string
{
    if (empty($_SESSION['guest_id'])) {
        $_SESSION['guest_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_id'];
}

/**
 * Set remember-me cookie for persistent login
 */
function setRememberMe(int $userId): void
{
    $token = generateToken();
    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
    $stmt->execute([hash('sha256', $token), $userId]);

    $expires = time() + (REMEMBER_COOKIE_DAYS * 86400);
    setcookie(REMEMBER_COOKIE_NAME, $userId . ':' . $token, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Clear remember-me cookie on logout
 */
function clearRememberMe(): void
{
    if (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
        setcookie(REMEMBER_COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/']);
    }
}

/**
 * Auto-login from remember-me cookie
 */
function checkRememberMe(): void
{
    if (isLoggedIn() || empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        return;
    }

    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
    if (count($parts) !== 2) {
        return;
    }

    [$userId, $token] = $parts;
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND remember_token = ? AND is_blocked = 0');
    $stmt->execute([(int)$userId, hash('sha256', $token)]);
    $user = $stmt->fetch();

    if ($user) {
        loginUser($user);
    }
}

/**
 * Flash message system for one-time notifications
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
