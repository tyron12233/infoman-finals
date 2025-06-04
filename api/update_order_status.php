<?php
// public/api/update_order_status.php

header('Content-Type: application/json');
header('Accept: application/json');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 1)); // Path from public/api/ to project root
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli
require_once ROOT_PATH . '/includes/auth_functions.php';
require_once ROOT_PATH . '/includes/order_functions.php';

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST is accepted.']);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

$orderId = isset($input['order_id']) ? filter_var($input['order_id'], FILTER_VALIDATE_INT) : null;
$newStatus = isset($input['new_status']) ? trim($input['new_status']) : null;

if (!$orderId || empty($newStatus)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing order_id or new_status.']);
    exit;
}

// Validate $newStatus against allowed values (already done in updateOrderStatus, but good for API layer too)
$allowed_statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
if (!in_array($newStatus, $allowed_statuses)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => "Invalid status value. Allowed statuses are: " . implode(', ', $allowed_statuses)]);
    exit;
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    error_log("API Update Order Status: Database connection not available.");
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit;
}

$mysqli->begin_transaction();
try {
    $success = updateOrderStatus($mysqli, $orderId, $newStatus);

    if ($success) {
        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => "Order #$orderId status updated to $newStatus."]);
    } else {
        $mysqli->rollback();
        // Check if the order was not found or status was invalid (already checked)
        // For now, assume failure means something went wrong with DB or order not found
        http_response_code(404); // Or 500 if it's a general DB error
        echo json_encode(['success' => false, 'message' => "Failed to update order status. Order may not exist or status is already set."]);
    }
} catch (Exception $e) {
    $mysqli->rollback();
    error_log("API Update Order Status Exception: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An internal error occurred while updating order status.']);
} finally {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
?>