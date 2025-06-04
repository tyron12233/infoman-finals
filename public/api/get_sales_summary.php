<?php
// public/api/get_sales_summary.php

header('Content-Type: application/json');
header('Accept: application/json');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2)); // Path from public/api/ to project root
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli
require_once ROOT_PATH . '/includes/auth_functions.php';
require_once ROOT_PATH . '/includes/sales_functions.php'; // Our new sales functions

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    error_log("API Get Sales Summary: Database connection not available.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit;
}

$salesSummary = getSalesSummary($mysqli);

if (empty($salesSummary) && $mysqli->error) { // Check if empty due to DB error
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching sales summary from database.']);
} elseif (empty($salesSummary) && !$mysqli->error) { // No sales data, but no DB error
    echo json_encode([
        'success' => true,
        'summary' => [
            'total_sales_amount' => 0.00,
            'total_orders_count' => 0,
            'average_order_value' => 0.00,
            'recent_completed_orders' => [],
            'top_selling_products' => [],
        ]
    ]);
} else {
    echo json_encode(['success' => true, 'summary' => $salesSummary]);
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>