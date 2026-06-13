<?php
/**
 * MAB Shop - Application Configuration
 * Central settings for the e-commerce application
 */

declare(strict_types=1);

// Application identity
define('APP_NAME', 'MAB Shop');
define('APP_URL', 'http://localhost/Shop');
define('APP_VERSION', '1.0.0');
define('APP_CURRENCY', 'GHS');
define('APP_CURRENCY_SYMBOL', 'GH₵');

// Path constants
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UPLOADS_URL', APP_URL . '/uploads');

// Security settings
define('CSRF_TOKEN_NAME', 'mab_csrf_token');
define('SESSION_NAME', 'MAB_SHOP_SESSION');
define('REMEMBER_COOKIE_NAME', 'mab_remember');
define('REMEMBER_COOKIE_DAYS', 30);

// Tax and shipping
define('TAX_RATE', 0.125); // 12.5% VAT
define('FREE_SHIPPING_THRESHOLD', 500.00);
define('STANDARD_SHIPPING_COST', 25.00);

// Email settings (configure for production)
define('MAIL_FROM', 'noreply@mabshop.com');
define('MAIL_FROM_NAME', 'MAB Shop');

// Social login placeholders (ready for OAuth integration)
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('FACEBOOK_APP_ID', '');
define('FACEBOOK_APP_SECRET', '');

// Payment gateway placeholders
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_MODE', 'sandbox');

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Africa/Accra');

// Error reporting (disable in production)
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
