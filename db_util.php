<?php
// PHP MySQL Connector Utility
// ============================
// This script establishes a connection to a MySQL database using PHP's mysqli extension
// and makes the connection object ($mysqli) available for use in the including script.
//
// Ensure that the 'php-mysql' extension is enabled in your PHP installation.
// The setup scripts (setup_linux.sh / setup_windows.ps1) attempt to install this.
//
// --- How to Use ---
// 1. Configure your database credentials below.
// 2. Include this script in your other PHP files:
//    require_once 'path/to/this_script.php';
// 3. After including, you can use the $mysqli object to perform queries:
//    $result = $mysqli->query("SELECT * FROM your_table");
//    // ... process $result ...
// 4. Remember to close the connection when appropriate in your main script:
//    $mysqli->close();

// --- Database Configuration ---
// Replace with your actual database credentials.
$dbHost = "localhost";    // Or "127.0.0.1"
$dbPort = 3306;           // Default MySQL port, change if yours is different
$dbUsername = "root";         // Replace with your MySQL username
$dbPassword = "";             // Replace with your MySQL password, or "" if no password is set
$dbName = "main"; // Optional: Replace with your specific database name.
// If set, the connection will use this database by default.

// --- Helper Functions for Output (used during connection) ---
if (!function_exists('print_db_info')) { // Prevent re-declaration if included multiple times (though require_once is better)
    function print_db_info($message)
    {
        if (php_sapi_name() === 'cli') {
            echo "[INFO] " . $message . "\n";
        } else {
            // For web output, you might log this or display it differently,
            // but generally, a connector script wouldn't output HTML directly.
            error_log("[INFO] DB Connector: " . $message);
        }
    }
}

if (!function_exists('print_db_success')) {
    function print_db_success($message)
    {
        if (php_sapi_name() === 'cli') {
            echo "[SUCCESS] " . $message . "\n";
        } else {
            error_log("[SUCCESS] DB Connector: " . $message);
        }
    }
}

if (!function_exists('print_db_error')) {
    function print_db_error($message)
    {
        $errorMessage = "[ERROR] DB Connector: " . $message;
        if (php_sapi_name() === 'cli') {
            // ANSI escape codes for red color (for CLI)
            $redColorStart = "\033[31m";
            $colorEnd = "\033[0m";
            echo $redColorStart . $errorMessage . $colorEnd . "\n";
        } else {
            // For web output, log the error. Avoid echoing directly in a library script.
            error_log($errorMessage);
        }
    }
}

// --- Establish Database Connection ---
print_db_info("Attempting to connect to MySQL server at $dbHost:$dbPort...");

// Create a new mysqli connection object
// The '@' symbol suppresses PHP's default error handling for this function,
// allowing us to handle it manually.
$mysqli = @new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

// Check for connection errors
if ($mysqli->connect_error) {
    print_db_error("Connection failed: " . $mysqli->connect_error);
    print_db_error("Please check your database credentials, ensure MySQL server is running, and the php-mysql extension is enabled.");
    // In a web application, you might throw an exception or handle this more gracefully
    // than just exiting, but for a utility script that's a dependency, exiting on failure is common.
    exit; // Terminate script if connection fails
}

print_db_success("Successfully connected to MySQL server!");
print_db_info("MySQL Server Version: " . $mysqli->server_info);

if (!empty($dbName) && $mysqli->select_db($dbName)) {
    print_db_info("Default database selected: '$dbName'");
} elseif (!empty($dbName)) {
    print_db_error("Failed to select default database: '$dbName'. Error: " . $mysqli->error);
    // Decide if this is a fatal error. For now, we'll allow connection to server even if specific DB fails.
    // The $mysqli object will still be available.
} else {
    print_db_info("Connected to MySQL server (no specific default database selected during connection).");
}

// The $mysqli object is now available for use in the script that included this file.
// Example (in the including script):
//
// require_once 'this_connector_script.php';
//
// if ($mysqli) {
//     $result = $mysqli->query("SELECT YOUR_COLUMN FROM YOUR_TABLE");
//     if ($result) {
//         while ($row = $result->fetch_assoc()) {
//             // process row
//         }
//         $result->free();
//     } else {
//         echo "Query failed: " . $mysqli->error;
//     }
//     $mysqli->close(); // Close connection when done
// }
?>