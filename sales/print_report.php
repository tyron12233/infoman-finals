<?php
// public/sales/print_report.php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db_connect.php'; // $mysqli
require_once ROOT_PATH . '/includes/auth_functions.php';
require_once ROOT_PATH . '/includes/sales_functions.php'; // For getComprehensiveSalesReport
require_once ROOT_PATH . '/includes/order_functions.php'; // For formatOrderDate

startSecureSession();

if (!isLoggedIn()) {
    // Optional: Redirect to login or show an error message
    echo "<p>Unauthorized access. Please <a href='../login/'>login</a>.</p>";
    exit;
}

// Get date range from query parameters if provided
$startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    echo "<p>Database connection error. Cannot generate report.</p>";
    exit;
}

$reportData = getComprehensiveSalesReport($mysqli, $startDate, $endDate);

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - <?php echo htmlspecialchars($reportData['report_period']); ?> - 4031 Cafe POS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
        }

        .report-container {
            width: 100%;
            max-width: 800px;
            /* Adjust as needed for A4 or letter */
            margin: 0 auto;
        }

        h1,
        h2,
        h3 {
            color: #1a1a1a;
            margin-bottom: 0.5em;
        }

        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 30px;
        }

        h3 {
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .summary-section,
        .details-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #fdfdfd;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .summary-item strong {
            color: #555;
        }

        .no-data {
            font-style: italic;
            color: #777;
        }

        .order-items-list {
            list-style-type: disc;
            padding-left: 20px;
            margin-top: 5px;
        }

        .order-items-list li {
            font-size: 0.9em;
        }

        @media print {
            body {
                margin: 0;
                color: #000;
            }

            .report-container {
                max-width: 100%;
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 1cm;
            }

            .summary-section,
            .details-section {
                border: none;
                padding: 0;
                background-color: #fff;
                margin-bottom: 15px;
            }

            h1,
            h2,
            h3 {
                color: #000;
            }

            a {
                text-decoration: none;
                color: #000;
            }

            /* Hide elements not for printing */
            .no-print {
                display: none !important;
            }

            table {
                font-size: 10pt;
            }

            th,
            td {
                padding: 4px;
            }
        }
    </style>
</head>

<body>
    <div class="report-container">
        <h1>4031 Cafe - Sales Report</h1>
        <p class="no-print" style="text-align:center; margin-bottom:20px;">
            <button onclick="window.print()">Print this Report</button>
        </p>

        <section class="summary-section">
            <h2>Report Summary</h2>
            <div class="summary-item"><span>Report Period:</span>
                <strong><?php echo htmlspecialchars($reportData['report_period']); ?></strong>
            </div>
            <div class="summary-item"><span>Total Revenue:</span> <strong>P
                    <?php echo number_format($reportData['total_revenue'], 2); ?></strong></div>
            <div class="summary-item"><span>Total Orders (Completed/Paid):</span>
                <strong><?php echo $reportData['total_orders']; ?></strong>
            </div>
            <div class="summary-item"><span>Total Items Sold:</span>
                <strong><?php echo $reportData['itemized_sales_count']; ?></strong>
            </div>
            <?php if ($reportData['total_orders'] > 0): ?>
                <div class="summary-item"><span>Average Order Value:</span> <strong>P
                        <?php echo number_format($reportData['total_revenue'] / $reportData['total_orders'], 2); ?></strong>
                </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($reportData['sales_by_product'])): ?>
            <section class="details-section">
                <h2>Sales by Product</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product (Variant)</th>
                            <th>Quantity Sold</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['sales_by_product'] as $productName => $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($productName); ?></td>
                                <td><?php echo $data['quantity_sold']; ?></td>
                                <td>P <?php echo number_format($data['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if (!empty($reportData['orders'])): ?>
            <section class="details-section">
                <h2>Detailed Orders</h2>
                <?php foreach ($reportData['orders'] as $order): ?>
                    <div style="margin-bottom: 15px; padding-bottom:10px; border-bottom: 1px dashed #ccc;">
                        <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?>
                            (<?php echo htmlspecialchars($order['order_type']); ?>)</h3>
                        <p><strong>Date:</strong> <?php echo formatOrderDate($order['created_at']); ?></p>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?: 'N/A'); ?></p>
                        <p><strong>Total Amount:</strong> P <?php echo number_format($order['total_amount'], 2); ?></p>
                        <?php if (!empty($order['items'])): ?>
                            <strong>Items:</strong>
                            <ul class="order-items-list">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li>
                                        <?php echo $item['quantity']; ?> x
                                        <?php echo htmlspecialchars($item['product_name_at_purchase']); ?>
                                        @ P <?php echo number_format($item['price_at_purchase'], 2); ?>
                                        (Subtotal: P <?php echo number_format($item['item_total'], 2); ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="no-data">No item details for this order.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <p class="no-data">No orders found for the selected period.</p>
        <?php endif; ?>

    </div>
</body>

</html>