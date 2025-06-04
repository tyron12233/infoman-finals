<?php
// public/api/get_orders.php

header('Content-Type: application/json');
header('Accept: application/json');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 1)); // Adjust if your public/api is structured differently
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli
require_once ROOT_PATH . '/includes/auth_functions.php';
require_once ROOT_PATH . '/includes/order_functions.php';

startSecureSession();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

// Check if $mysqli is available
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    error_log("API Get Orders: Database connection not available.");
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit;
}

$orders = fetchAllOrdersWithDetails($mysqli);

if ($mysqli->error) {
    echo json_encode(['success' => false, 'message' => 'Error fetching orders from database.', 'db_error' => $mysqli->error]);
} else {
    echo json_encode(['success' => true, 'orders' => $orders]);
}

// Close connection if $mysqli is scoped here and opened by db_connect.php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>