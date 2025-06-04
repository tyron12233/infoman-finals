<?php
// public/sales/index.php - Sales Page Controller

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 1)); // Path from public/sales/ to project root
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/includes/auth_functions.php';
// Database connection and sales functions will be handled by the API

startSecureSession();

if (!isLoggedIn()) {
    redirect('../login/'); // Redirect to login if not authenticated
}

$pageTitle = "Sales Overview - 4031 Cafe POS";
$content_template = ROOT_PATH . '/templates/sales_content.php';
$page_specific_js = 'js/sales.js'; // Specify the JS file for this page

require_once ROOT_PATH . '/templates/layout_dynamic.php'; // Use the dynamic layout
?>