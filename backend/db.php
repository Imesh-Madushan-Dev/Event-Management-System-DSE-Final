<?php
/**
 * Database Connection
 * -------------------
 * Establishes a MySQLi connection to the NIBM Events database.
 * Included by all backend scripts via require_once.
 */

$host     = 'localhost';
$username = 'root';
$password = '';
$database = 'nibm_events';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error(),
    ]));
}

// Set charset for proper encoding
mysqli_set_charset($conn, 'utf8mb4');
