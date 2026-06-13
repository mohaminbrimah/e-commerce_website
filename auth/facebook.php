<?php
/**
 * MAB Shop - Facebook OAuth Login (Ready for integration)
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!FACEBOOK_APP_ID) {
    setFlash('info', 'Facebook login is not configured yet. Add your App ID in config/app.php');
    redirect(url('login.php'));
}

$authUrl = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query([
    'client_id' => FACEBOOK_APP_ID,
    'redirect_uri' => url('auth/facebook-callback.php'),
    'scope' => 'email,public_profile',
]);
redirect($authUrl);
