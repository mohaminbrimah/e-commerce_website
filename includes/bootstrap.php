<?php
/**
 * MAB Shop - Application Bootstrap
 * Loads configuration, starts session, and initializes core components
 */

declare(strict_types=1);

// Load application config and database
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

// Load core includes
require_once INCLUDES_PATH . '/security.php';
require_once INCLUDES_PATH . '/session.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

// Autoload model classes
spl_autoload_register(function (string $class): void {
    $file = ROOT_PATH . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Start secure session
startSecureSession();

// Generate CSRF token if not exists
if (empty($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = generateToken();
}
