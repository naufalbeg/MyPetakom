<?php
// db_connect.php

// Database credentials
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "mypetakom";
$port       = 3310;

// Create MySQLi connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set UTF-8 charset
if (! $conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: {$conn->error}");
}
?>
