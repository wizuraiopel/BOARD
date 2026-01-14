<?php
// config/config.php

// --- Environment ---
define('ENVIRONMENT', 'development'); // Or 'production'

// --- Paths ---
define('ROOT_PATH', dirname(__DIR__) . '/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('VIEWS_PATH', ROOT_PATH . 'views/');
define('CONTROLLERS_PATH', ROOT_PATH . 'controllers/');
define('MODELS_PATH', ROOT_PATH . 'models/');

// --- Database Config ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'tmlhubco_dev_board');
define('DB_USER', 'tmlhubco_superadmin');
define('DB_PASS', 'Tml5796@');
define('DB_CHARSET', 'utf8mb4');

// --- App Settings ---
define('SITE_NAME', 'Dev B.O.A.R.D');
// CORRECTED: Removed trailing spaces
define('BASE_URL', 'https://dev.board.tmlhub.com'); // No trailing spaces

// --- Security ---
define('SESSION_LIFETIME', 3600); // Session timeout in seconds (1 hour)
define('CSRF_TOKEN_NAME', 'csrf_token'); // Name of the CSRF token field

// Error reporting for development
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Production: Log errors, don't display them
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}