<?php
// includes/order_functions.php

/**
 * Fetches all orders with their items.
 *
 * @param mysqli $mysqli The database connection object.
 * @return array An array of orders, each with an 'items' array.
 */
function fetchAllOrdersWithDetails(mysqli $mysqli): array
{
    $orders = [];
    // First, fetch all main order details
    // Consider adding pagination for large numbers of orders in a real application
    $order_sql = "SELECT o.id as order_id, o.order_number, o.customer_name, o.order_type, o.total_amount, o.status, o.created_at
                  FROM orders o
                  ORDER BY o.created_at DESC"; // Show newest orders first

    $order_result = $mysqli->query($order_sql);
    if (!$order_result) {
        error_log("Failed to fetch orders: " . $mysqli->error);
        return [];
    }

    $orders_temp = [];
    while ($order_row = $order_result->fetch_assoc()) {
        $order_row['items'] = []; // Initialize items array
        $orders_temp[$order_row['order_id']] = $order_row;
    }
    $order_result->free();

    if (empty($orders_temp)) {
        return [];
    }

    // Now, fetch all order items for the retrieved orders
    // Ensure keys are integers for IN clause if they are numeric
    $order_ids_array = array_map('intval', array_keys($orders_temp));
    if (empty($order_ids_array)) { // Should not happen if $orders_temp is not empty, but good check
        return array_values($orders_temp);
    }
    $order_ids_string = implode(',', $order_ids_array);

    $item_sql = "SELECT oi.order_id, oi.product_name_at_purchase, oi.quantity, oi.price_at_purchase, oi.notes
                 FROM order_items oi
                 WHERE oi.order_id IN ($order_ids_string)
                 ORDER BY oi.id ASC";

    $item_result = $mysqli->query($item_sql);
    if (!$item_result) {
        error_log("Failed to fetch order items: " . $mysqli->error);
        return array_values($orders_temp);
    }

    while ($item_row = $item_result->fetch_assoc()) {
        if (isset($orders_temp[$item_row['order_id']])) {
            $orders_temp[$item_row['order_id']]['items'][] = $item_row;
        }
    }
    $item_result->free();

    return array_values($orders_temp);
}

/**
 * Updates the status of a specific order.
 *
 * @param mysqli $mysqli The database connection object.
 * @param int $orderId The ID of the order to update.
 * @param string $newStatus The new status for the order.
 * @return bool True on success, false on failure.
 */
function updateOrderStatus(mysqli $mysqli, int $orderId, string $newStatus): bool
{
    // Optional: Validate $newStatus against a list of allowed statuses
    $allowed_statuses = ['Pending', 'Processing', 'Completed', 'Cancelled']; // Match your schema/logic
    if (!in_array($newStatus, $allowed_statuses)) {
        error_log("Invalid status update attempted for order ID $orderId: $newStatus");
        return false;
    }

    $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed for updateOrderStatus: " . $mysqli->error);
        return false;
    }
    $stmt->bind_param("si", $newStatus, $orderId);
    $success = $stmt->execute();
    if (!$success) {
        error_log("Execute failed for updateOrderStatus: " . $stmt->error);
    }
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    return $success && $affected_rows > 0; // Ensure a row was actually updated
}


/**
 * Formats a date string.
 * @param string|null $date_string The date string to format.
 * @param string $format The desired output format.
 * @return string The formatted date or a placeholder.
 */
function formatOrderDate(?string $date_string, string $format = 'M j, Y, g:i A'): string
{
    if ($date_string) {
        try {
            $date = new DateTime($date_string);
            return $date->format($format);
        } catch (Exception $e) {
            return 'Invalid Date';
        }
    }
    return 'N/A';
}

/**
 * Gets a display-friendly status name.
 * @param string|null $status_key The status key from the database.
 * @return string The display name for the status.
 */
function getOrderStatusDisplay(?string $status_key): string
{
    $statuses = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'paid' => 'Paid',
    ];
    return $statuses[strtolower($status_key ?? '')] ?? ucfirst($status_key ?? 'Unknown');
}

?>