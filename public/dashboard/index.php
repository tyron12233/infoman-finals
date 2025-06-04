<?php
// public/dashboard/index.php - POS System UI Controller

// --- Bootstrap ---
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2)); // Goes two levels up from public/dashboard/
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli object
require_once ROOT_PATH . '/includes/auth_functions.php';
require_once ROOT_PATH . '/includes/data_functions.php'; // Your functions like fetchCategories, etc.

startSecureSession();

// --- Authentication Check ---
if (!isLoggedIn()) {
    // Path from public/dashboard/index.php to public/login/index.php is ../login/
    redirect('../login/');
}

// --- Fetch Data ---
// These functions are from 'includes/data_functions.php'
$categories = fetchCategories($mysqli);
$raw_products = fetchProductsWithVariants($mysqli);
$products_data = groupProducts($raw_products); // This already handles grouping
$selected_category_id = getSelectedCategoryId($categories); // Determines selected category

// --- Page Specific Variables ---
$pageTitle = "4031 Cafe POS - Dashboard";

// --- User Information (Optional, for display in template if needed) ---
// $user_full_name = $_SESSION[SESSION_USER_NAME_KEY] ?? 'User';
// $user_role = $_SESSION[SESSION_USER_ROLE_KEY] ?? 'guest';

// --- Render View ---
// The main layout file `templates/layout.php` will be responsible for including:
// - partials/sidebar.php
// - index_content.php (which contains the main POS UI structure)
// - partials/order_summary.php
// - partials/dialog.php
// It also links to assets/css/style.css and assets/js/main.js
// The variables $pageTitle, $categories, $products_data, $selected_category_id
// will be available in the scope of the included files.

$content_template = ROOT_PATH . '/templates/index_content.php'; // Main content for the dashboard
$page_specific_js = 'js/dashboard.js'; // JS specific to this page,


require_once ROOT_PATH . '/templates/layout_dynamic.php';

// --- Close Database Connection ---
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>