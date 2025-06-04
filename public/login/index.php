<?php
// public/login/index.php

// --- Bootstrap ---
// Define ROOT_PATH for easier inclusion if not already defined by a front controller
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2)); // Goes two levels up from public/login/ to project root
}

require_once ROOT_PATH . '/config/config.php'; // Defines session keys
require_once ROOT_PATH . '/includes/auth_functions.php'; // Authentication functions
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli object

startSecureSession(); // Start or resume session

// --- Redirect if already logged in ---
if (isLoggedIn()) {
    // Assuming your main application/dashboard is at project_root/public/index.php or project_root/public/dashboard/index.php
    // If BASE_URL is defined: redirect('dashboard/'); or redirect('');
    // Relative path from public/login/index.php to public/index.php is ../index.php
    redirect('../index.php'); // Redirect to the main POS page (which might be a router)
}

// --- Login Logic ---
$login_error = "";
$submitted_username = ""; // To repopulate the username field on error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $submitted_username = $username; // Store for repopulation

    $login_attempt = attemptLogin($mysqli, $username, $password);

    if ($login_attempt['success']) {
        // Redirect to the main router/dashboard
        // Relative path from public/login/index.php to public/index.php is ../index.php
        redirect('../index.php');
    } else {
        $login_error = $login_attempt['message'];
    }
}

// --- Page Specific Variables for the Template ---
$pageTitle = "Login - 4031 Cafe POS";

// --- Render View ---
// The variables $pageTitle, $login_error, $submitted_username will be available
// in the scope of the included login_layout.php and subsequently login_form.php.
require_once ROOT_PATH . '/templates/login_layout.php';

// --- Close Database Connection ---
// db_connect.php might handle this, or PHP handles it at script end.
// If $mysqli is scoped to db_connect.php, it might already be closed.
// If $mysqli is global here, you can close it.
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>