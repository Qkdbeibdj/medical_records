<?php
$servername = "localhost";
$username = "root";
$password = ""; // default for XAMPP
$database = "medical_records";
$charset = 'utf8mb4';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode([ 
        'success' => false, 
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Set charset for the connection
$conn->set_charset($charset);
?>
