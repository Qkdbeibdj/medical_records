<?php
require '../includes/db_connect.php';
header('Content-Type: application/json');

// Query to fetch tests from the medical_tests table
$query = "SELECT test_id, test_name FROM medical_tests ORDER BY test_name";
$result = $conn->query($query);

$tests = [];
while ($row = $result->fetch_assoc()) {
    $tests[] = $row;
}

// Return the list of tests as JSON
echo json_encode($tests);
?>
