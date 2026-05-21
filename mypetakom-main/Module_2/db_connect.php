<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "mypetakom";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
