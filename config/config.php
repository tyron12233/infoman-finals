<?php
// config/config.php

// --- Error Reporting ---
// For development:
error_reporting(E_ALL);
ini_set('display_errors', 1);
// For production, you might set:
// error_reporting(0);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/../logs/php-error.log'); // Ensure 'logs' directory exists and is writable

// --- Base URL (Optional but Recommended) ---
// Define this if you want to use absolute URLs for redirects and assets.
// Useful if your app isn't at the web root.
// Example: define('BASE_URL', 'http://localhost/your_pos_project_folder/');
// If your app is at the root of your domain, it might be:
// define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/");


// --- Session Configuration ---
define('SESSION_USER_ID_KEY', 'user_id');
define('SESSION_USER_NAME_KEY', 'user_full_name');
define('SESSION_USER_ROLE_KEY', 'user_role');



// debug mode
define('BASE_URL', 'http://localhost:8000/');

// Project root path, adjust if needed
// --- Database Connection (Details are in db_connect.php) ---
// No need to define DB constants here if db_connect.php handles it directly.

// --- Other Global Settings ---
// define('SITE_NAME', '4031 Cafe POS');

?>