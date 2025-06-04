<?php
// public/orders/index.php - View Orders Page Controller

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 1));
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/includes/auth_functions.php';

startSecureSession();

if (!isLoggedIn()) {
    redirect('../login/');
}

$pageTitle = "View Orders - 4031 Cafe POS";
$content_template = ROOT_PATH . '/templates/orders_content.php';
$page_specific_js = 'js/orders.js'; // Specify the JS file for this page

require_once ROOT_PATH . '/templates/layout_dynamic.php';
?>