<?php
$host = "127.0.0.1"; // Change if your database is on a remote server
$user = "root"; // Your MySQL username (default is "root")
$password = ""; // Your MySQL password (default is empty)
$database = "triptrove"; // Your database name

// Create a connection (try default port 3306, fallback to 3307)
try {
    $conn = mysqli_connect($host, $user, $password, $database, 3306);
} catch (mysqli_sql_exception $e) {
    $conn = mysqli_connect($host, $user, $password, $database, 3307);
}

// Check connection
if (!$conn) 
{
    die("Connection failed: " . mysqli_connect_error());
}
?>
