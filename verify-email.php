<?php
/**
 * MAB Shop - Email Verification Handler
 */
require_once __DIR__ . '/includes/bootstrap.php';

$token = sanitizeInput($_GET['token'] ?? '');
if ($token && verifyEmail($token)) {
    setFlash('success', 'Email verified successfully! You can now log in.');
} else {
    setFlash('danger', 'Invalid or expired verification link.');
}
redirect(url('login.php'));
