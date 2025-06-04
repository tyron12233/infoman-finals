<?php
// includes/sales_functions.php

/**
 * Fetches a summary of sales data.
 *
 * @param mysqli $mysqli The database connection object.
 * @return array An array containing sales summary.
 */
function getSalesSummary(mysqli $mysqli): array
{
    $summary = [
        'total_sales_amount' => 0.00,
        'total_orders_count' => 0,
        'average_order_value' => 0.00,
        'recent_completed_orders' => [],
    ];

    $sql = "SELECT 
                SUM(total_amount) as total_revenue, 
                COUNT(id) as total_orders 
            FROM orders 
            WHERE status IN ('Completed', 'Paid')";
    $result = $mysqli->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $summary['total_sales_amount'] = (float) ($row['total_revenue'] ?? 0.00);
        $summary['total_orders_count'] = (int) ($row['total_orders'] ?? 0);
        if ($summary['total_orders_count'] > 0) {
            $summary['average_order_value'] = $summary['total_sales_amount'] / $summary['total_orders_count'];
        }
        $result->free();
    } else {
        error_log("Failed to fetch sales summary: " . $mysqli->error);
        // Return default summary on error, but log it
    }

    $recent_orders_sql = "SELECT order_number, customer_name, total_amount, created_at 
                          FROM orders 
                          WHERE status IN ('Completed', 'Paid') 
                          ORDER BY created_at DESC 
                          LIMIT 5";
    $recent_orders_result = $mysqli->query($recent_orders_sql);
    if ($recent_orders_result) {
        while ($order_row = $recent_orders_result->fetch_assoc()) {
            $summary['recent_completed_orders'][] = $order_row;
        }
        $recent_orders_result->free();
    }

    $top_products_sql = "
    SELECT 
        oi.product_name_at_purchase, 
        SUM(oi.quantity) as total_quantity_sold,
        SUM(oi.quantity * oi.price_at_purchase) as total_revenue_from_product
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('Completed', 'Paid')
    GROUP BY oi.product_name_at_purchase
    ORDER BY total_quantity_sold DESC
    LIMIT 5";
    $top_products_result = $mysqli->query($top_products_sql);
    $summary['top_selling_products'] = [];
    if ($top_products_result) {
        while ($prod_row = $top_products_result->fetch_assoc()) {
            $summary['top_selling_products'][] = $prod_row;
        }
        $top_products_result->free();
    }

    return $summary;
}

/**
 * Fetches comprehensive data for a sales report.
 * This could include itemized sales, sales by category, etc.
 * For this example, we'll fetch all completed/paid orders with their items.
 *
 * @param mysqli $mysqli The database connection object.
 * @param string|null $startDate (YYYY-MM-DD)
 * @param string|null $endDate (YYYY-MM-DD)
 * @return array An array containing detailed sales report data.
 */
function getComprehensiveSalesReport(mysqli $mysqli, ?string $startDate = null, ?string $endDate = null): array
{
    $reportData = [
        'report_period' => 'All Time',
        'total_revenue' => 0.00,
        'total_orders' => 0,
        'itemized_sales_count' => 0,
        'orders' => [],
        'sales_by_product' => [],
        // Add more sections as needed, e.g., sales_by_category
    ];

    // Build WHERE clause for date range
    $dateWhereClause = "";
    $params = [];
    $types = "";

    if ($startDate && $endDate) {
        $reportData['report_period'] = "From $startDate to $endDate";
        $dateWhereClause = " AND o.created_at BETWEEN ? AND ?";
        // Adjust endDate to include the whole day
        $params[] = $startDate . " 00:00:00";
        $params[] = $endDate . " 23:59:59";
        $types .= "ss";
    } elseif ($startDate) {
        $reportData['report_period'] = "From $startDate onwards";
        $dateWhereClause = " AND o.created_at >= ?";
        $params[] = $startDate . " 00:00:00";
        $types .= "s";
    } elseif ($endDate) {
        $reportData['report_period'] = "Up to $endDate";
        $dateWhereClause = " AND o.created_at <= ?";
        $params[] = $endDate . " 23:59:59";
        $types .= "s";
    }


    // 1. Fetch all completed/paid orders within the date range
    $orderSql = "SELECT o.id as order_id, o.order_number, o.customer_name, o.order_type, 
                        o.total_amount, o.status, o.created_at
                 FROM orders o
                 WHERE o.status IN ('Completed', 'Paid')" . $dateWhereClause . "
                 ORDER BY o.created_at ASC";

    $stmtOrder = $mysqli->prepare($orderSql);
    if (!$stmtOrder) {
        error_log("Prepare failed for report orders: " . $mysqli->error);
        return $reportData;
    }
    if (!empty($types) && !empty($params)) {
        $stmtOrder->bind_param($types, ...$params);
    }
    $stmtOrder->execute();
    $orderResult = $stmtOrder->get_result();

    $ordersById = [];
    while ($row = $orderResult->fetch_assoc()) {
        $row['items'] = [];
        $ordersById[$row['order_id']] = $row;
        $reportData['total_revenue'] += (float) $row['total_amount'];
        $reportData['total_orders']++;
    }
    $stmtOrder->close();

    if (empty($ordersById)) {
        return $reportData; // No orders found for the period
    }

    // 2. Fetch items for these orders
    $orderIds = array_keys($ordersById);
    $orderIdsString = implode(',', array_map('intval', $orderIds));

    $itemSql = "SELECT oi.order_id, oi.product_name_at_purchase, oi.quantity, 
                       oi.price_at_purchase, (oi.quantity * oi.price_at_purchase) as item_total
                FROM order_items oi
                WHERE oi.order_id IN ($orderIdsString)";
    $itemResult = $mysqli->query($itemSql);
    if ($itemResult) {
        while ($itemRow = $itemResult->fetch_assoc()) {
            if (isset($ordersById[$itemRow['order_id']])) {
                $ordersById[$itemRow['order_id']]['items'][] = $itemRow;
            }
            // Aggregate sales by product
            $productName = $itemRow['product_name_at_purchase'];
            if (!isset($reportData['sales_by_product'][$productName])) {
                $reportData['sales_by_product'][$productName] = ['quantity_sold' => 0, 'total_revenue' => 0.00];
            }
            $reportData['sales_by_product'][$productName]['quantity_sold'] += (int) $itemRow['quantity'];
            $reportData['sales_by_product'][$productName]['total_revenue'] += (float) $itemRow['item_total'];
            $reportData['itemized_sales_count'] += (int) $itemRow['quantity'];
        }
        $itemResult->free();
    }
    $reportData['orders'] = array_values($ordersById);

    // Sort sales_by_product by total_revenue descending
    uasort($reportData['sales_by_product'], function ($a, $b) {
        return $b['total_revenue'] <=> $a['total_revenue'];
    });

    return $reportData;
}

?>