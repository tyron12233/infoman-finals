<?php
// Database Connection Details
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'main';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    error_log("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
    // You might want to die here or handle this more gracefully depending on the application
    die("Database connection failed. Please try again later or contact support.");
}
$mysqli->set_charset("utf8mb4");
?>