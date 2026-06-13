<?php
/**
 * MAB Shop - Google OAuth Login (Ready for integration)
 * Configure GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in config/app.php
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!GOOGLE_CLIENT_ID) {
    setFlash('info', 'Google login is not configured yet. Add your OAuth credentials in config/app.php');
    redirect(url('login.php'));
}

// OAuth flow placeholder - integrate with Google API Client library
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => url('auth/google-callback.php'),
    'response_type' => 'code',
    'scope' => 'email profile',
]);
redirect($authUrl);
