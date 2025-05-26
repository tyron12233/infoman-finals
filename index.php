<?php
// index.php - Example usage of the MySQL Connector Utility

// Page Title
$pageTitle = "PHP MySQL Connection Test";

// --- 1. Include the MySQL Connector Utility ---
// Ensure 'php_mysql_connector_script.php' is in the correct path.
// If it's in the same directory, this is fine.
// If it's in, for example, an 'includes' folder, use 'includes/php_mysql_connector_script.php'.
require_once 'db_util.php'; // This brings in the $mysqli object

// At this point, the connector script will have attempted to connect to the database.
// If the connection failed, the connector script would have exited.
// So, if we reach here, $mysqli should be available and connected.

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
        }

        .status {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .query-results {
            margin-top: 20px;
        }

        ul {
            list-style-type: disc;
            margin-left: 20px;
        }

        pre {
            background-color: #eee;
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

        <?php
        // --- 2. Check if $mysqli object exists (it should if require_once didn't fail) ---
        if (isset($mysqli) && $mysqli instanceof mysqli) {
            echo "<div class='status success'>Successfully included connector. MySQL connection object is available.</div>";
            echo "<p><strong>MySQL Server Version:</strong> " . htmlspecialchars($mysqli->server_info) . "</p>";

            // --- 3. Perform a Simple Query ---
            echo "<div class='query-results'>";
            echo "<h2>Attempting a simple query...</h2>";

            // Example: List databases (useful if no specific $dbName was set in the connector)
            // Or, if $dbName was set in the connector, you could query a specific table.
            $query = "SHOW DATABASES;";
            // If you have a specific database selected in the connector (e.g., $dbName = "my_app_db";)
            // you might want to query a table instead:
            // $query = "SELECT * FROM some_table LIMIT 5;";
        
            $result = $mysqli->query($query);

            if ($result) {
                echo "<div class='status success'>Query executed successfully: <code>" . htmlspecialchars($query) . "</code></div>";
                if ($result->num_rows > 0) {
                    echo "<p><strong>Results:</strong></p>";
                    echo "<ul>";
                    // For SHOW DATABASES, the column is 'Database'
                    // For other queries, adjust the column name accordingly.
                    while ($row = $result->fetch_assoc()) {
                        // Check which keys are available in the row
                        if (isset($row['Database'])) {
                            echo "<li>" . htmlspecialchars($row['Database']) . "</li>";
                        } else {
                            // Generic display for other queries
                            echo "<li><pre>" . htmlspecialchars(print_r($row, true)) . "</pre></li>";
                        }
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Query returned no rows.</p>";
                }
                $result->free(); // Free the result set
            } else {
                echo "<div class='status error'>Error executing query: <code>" . htmlspecialchars($query) . "</code><br>";
                echo "<strong>MySQL Error:</strong> " . htmlspecialchars($mysqli->error) . "</div>";
            }
            echo "</div>"; // end .query-results
        
            // --- 4. Close the MySQL Connection ---
            // It's good practice to close the connection when you're done with database operations.
            $mysqli->close();
            echo "<p style='margin-top: 20px; font-style: italic;'>MySQL connection closed.</p>";

        } else {
            // This case should ideally not be reached if the connector script exits on fatal connection error.
            echo "<div class='status error'>Failed to establish MySQL connection or $mysqli object not available. Check connector script.</div>";
        }
        ?>
    </div>
</body>

</html>