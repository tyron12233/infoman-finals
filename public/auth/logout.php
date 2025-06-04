<?php
// public/auth/logout.php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2)); // Goes two levels up from public/auth/
}

require_once ROOT_PATH . '/config/config.php'; // For session key constants if needed by auth_functions
require_once ROOT_PATH . '/includes/auth_functions.php';

logoutUser(); // This function handles session destruction

// Redirect to login page
// Path from public/auth/logout.php to public/login/index.php is ../login/index.php
redirect('../login/'); // The redirect function will handle BASE_URL if defined
?>