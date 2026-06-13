<?php
/**
 * MAB Shop - Logout Handler
 */
require_once __DIR__ . '/includes/bootstrap.php';
logoutUser();
setFlash('success', 'You have been logged out.');
redirect(url('index.php'));
