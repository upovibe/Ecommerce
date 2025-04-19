<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ecommerce_template');

$conn = null;
$db_connected = false;

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$conn->connect_error) {
        $db_connected = true;
        $conn->set_charset('utf8mb4');
    }
} catch (Exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    $conn = null;
    $db_connected = false;
}