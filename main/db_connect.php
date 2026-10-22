<?php
$host = "127.0.0.1"; // Change if your database is on a remote server
$user = "root"; // Your MySQL username (default is "root")
$password = ""; // Your MySQL password (default is empty)
$database = "triptrove"; // Your database name

// Create a connection (try default port 3306, fallback to 3307)
try {
    $conn = new mysqli($host, $user, $password, $database, 3306);
} catch (mysqli_sql_exception $e) {
    $conn = new mysqli($host, $user, $password, $database, 3307);
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
