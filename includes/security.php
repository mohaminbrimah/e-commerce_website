<?php
/**
 * MAB Shop - Security Utilities
 * CSRF protection, XSS sanitization, and input validation
 */

declare(strict_types=1);

/**
 * Generate a cryptographically secure random token
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Verify CSRF token from POST request
 */
function verifyCsrfToken(?string $token): bool
{
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Output CSRF hidden input field for forms
 */
function csrfField(): string
{
    $token = htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

/**
 * Sanitize output to prevent XSS attacks
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize user input string
 */
function sanitizeInput(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Require valid CSRF token or abort with 403
 */
function requireCsrf(): void
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        if (isAjaxRequest()) {
            jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        die('Invalid security token. Please refresh and try again.');
    }
}

/**
 * Check if request is AJAX/fetch
 */
function isAjaxRequest(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response and exit
 */
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Rate limit check (simple session-based)
 */
function checkRateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    $now = time();
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 0, 'start' => $now];
    }

    $limit = &$_SESSION['rate_limit'][$key];
    if ($now - $limit['start'] > $windowSeconds) {
        $limit = ['count' => 0, 'start' => $now];
    }

    $limit['count']++;
    return $limit['count'] <= $maxAttempts;
}
