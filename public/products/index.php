<?php
// public/products/index.php - Manage Products Page Controller

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2)); // Path from public/products/ to project root
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/includes/auth_functions.php';
// DB connection will be handled by the API endpoints

startSecureSession();

if (!isLoggedIn()) {
    redirect('../login/');
}

$pageTitle = "Manage Products - 4031 Cafe POS";
$content_template = ROOT_PATH . '/templates/products_content.php';
$page_specific_js = 'js/products.js'; // Specify the JS file for this page

require_once ROOT_PATH . '/templates/layout_dynamic.php'; // Use the dynamic layout
?>