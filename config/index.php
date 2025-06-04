<?php
// public/index.php

// --- Bootstrap ---
// Assuming your autoloader or direct includes are relative to the project root
// Adjust paths if your public/index.php is in a different location relative to these files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db_connect.php'; // $mysqli should be available
require_once __DIR__ . '/../includes/data_functions.php';

require_once __DIR__ . '/../includes/auth_functions.php'; // Authentication functions
// require_once __DIR__ . '/../includes/helpers.php'; // If you create it



// --- Page Specific Variables ---
$pageTitle = "4031 Cafe POS";

$isAlreadyLoggedIn = isLoggedIn();

if ($isAlreadyLoggedIn) {
    redirect('dashboard/'); // Redirect to dashboard if already logged in
} else {
    // If not logged in, redirect to login page
    redirect('login/');
}
?>