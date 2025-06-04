<?php
// place_order.php

// Set headers for JSON response
header('Content-Type: application/json');
header('Accept: application/json');

// --- Bootstrap ---
if (!defined('ROOT_PATH')) {
    // If this script is in the project root, ROOT_PATH is just __DIR__
    // If it's in public/api/, then define('ROOT_PATH', dirname(__DIR__, 2));
    define('ROOT_PATH', __DIR__); // Assuming place_order.php is in the project root
}

require_once ROOT_PATH . '/../config/config.php';
require_once ROOT_PATH . '/../config/db_connect.php'; // $mysqli object
require_once ROOT_PATH . '/../includes/auth_functions.php'; // For isLoggedIn and session user ID

startSecureSession();

// --- Authentication Check ---
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login to place an order.']);
    exit;
}

// --- Request Method Check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST is accepted.']);
    exit;
}

// --- Input Handling & Validation ---
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); // Convert JSON to associative array

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

// Basic validation (expand as needed)
$customerName = isset($input['customerName']) ? trim($input['customerName']) : null;
$orderType = isset($input['orderType']) ? trim($input['orderType']) : null; // Should match ENUM('Dine In', 'Take Out')
$items = isset($input['items']) && is_array($input['items']) ? $input['items'] : [];
$totalAmount = isset($input['totalAmount']) ? filter_var($input['totalAmount'], FILTER_VALIDATE_FLOAT) : false;
// $currentUserId = $_SESSION[SESSION_USER_ID_KEY] ?? null; // User ID is not in the new 'orders' schema

if (empty($orderType) || !in_array($orderType, ['Dine In', 'Take Out'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order type. Must be "Dine In" or "Take Out".']);
    exit;
}

if (empty($items) || $totalAmount === false || $totalAmount < 0) { // Allow 0 total amount if that's possible (e.g. fully discounted)
    echo json_encode(['success' => false, 'message' => 'Missing or invalid order data. Items and a valid total amount are required.']);
    exit;
}

// Further validation for each item
foreach ($items as $item) {
    if (
        !isset($item['variant_id']) || !filter_var($item['variant_id'], FILTER_VALIDATE_INT) || // This is product_variant_id
        !isset($item['product_name_at_purchase']) || empty(trim($item['product_name_at_purchase'])) ||
        !isset($item['quantity']) || !filter_var($item['quantity'], FILTER_VALIDATE_INT) || $item['quantity'] <= 0 ||
        !isset($item['price_at_purchase']) || !filter_var($item['price_at_purchase'], FILTER_VALIDATE_FLOAT) || $item['price_at_purchase'] < 0
    ) {
        echo json_encode(['success' => false, 'message' => 'Invalid item data. Each item must have a valid variant ID, name, quantity, and price.']);
        exit;
    }
}


// --- Database Interaction (Transaction) ---
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    error_log("Place Order: Database connection not available.");
    echo json_encode(['success' => false, 'message' => 'Database connection error. Cannot process order.']);
    exit;
}

$mysqli->begin_transaction();

try {
    // 1. Generate Order Number
    $orderNumber = "ORD-" . date("YmdHis") . substr(uniqid(), -3);
    $orderStatus = 'Pending'; // Default status, matches schema comment

    // 2. Insert into `orders` table
    // `user_id` removed as it's not in the provided schema
    $stmtOrder = $mysqli->prepare(
        "INSERT INTO orders (order_number, customer_name, order_type, total_amount, status) VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmtOrder) {
        throw new Exception("Order statement prepare failed: " . $mysqli->error);
    }
    $stmtOrder->bind_param(
        "sssds", // sssds instead of sssdsi
        $orderNumber,
        $customerName,
        $orderType,
        $totalAmount,
        $orderStatus
        // $currentUserId // Removed
    );

    if (!$stmtOrder->execute()) {
        throw new Exception("Execute failed for orders table: " . $stmtOrder->error);
    }
    $newOrderId = $mysqli->insert_id;
    $stmtOrder->close();

    // 3. Insert into `order_items` table
    // `product_id` and `sku_at_purchase` removed as they are not in the provided schema
    $stmtItem = $mysqli->prepare(
        "INSERT INTO order_items (order_id, product_variant_id, product_name_at_purchase, quantity, price_at_purchase, notes)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtItem) {
        throw new Exception("Order items statement prepare failed: " . $mysqli->error);
    }

    foreach ($items as $item) {
        $productVariantId = $item['variant_id']; // Renamed for clarity, matches JS 'variant_id'
        // $productId = $item['product_id'] ?? null; // Not in schema
        // $skuAtPurchase = $item['sku_at_purchase'] ?? null; // Not in schema
        $productNameAtPurchase = $item['product_name_at_purchase'];
        $quantity = $item['quantity'];
        $priceAtPurchase = $item['price_at_purchase'];
        $notes = isset($item['notes']) ? trim($item['notes']) : null;

        $stmtItem->bind_param(
            "iisids", // iisids instead of iiissids
            $newOrderId,
            $productVariantId,
            // $productId, // Removed
            // $skuAtPurchase, // Removed
            $productNameAtPurchase,
            $quantity,
            $priceAtPurchase,
            $notes
        );
        if (!$stmtItem->execute()) {
            throw new Exception("Execute failed for order_items table: " . $stmtItem->error . " for item: " . $productNameAtPurchase);
        }
    }
    $stmtItem->close();

    // 4. Commit the transaction
    $mysqli->commit();

    // --- Success Response ---
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'orderId' => $newOrderId,
        'orderNumber' => $orderNumber,
        // 'newOrderNumber' can be removed if orderNumber is sufficient for UI updates
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    error_log("Order placement failed: " . $e->getMessage());
    // Provide a more user-friendly message for production, but keep details for logging
    echo json_encode(['success' => false, 'message' => 'Failed to place order. An internal error occurred. Please try again.']);
    // For debugging, you might want to include $e->getMessage() in the JSON response, but be careful in production.
    // echo json_encode(['success' => false, 'message' => 'Failed to place order. Details: ' . $e->getMessage()]);
} finally {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
?>