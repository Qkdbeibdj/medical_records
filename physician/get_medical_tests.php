<?php
include '../includes/db_connect.php';

// This file should return ALL tests for the dropdown — no test_id needed
$query = "SELECT test_id, test_name FROM medical_tests";
$result = $conn->query($query);

$tests = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tests[] = [
            'test_id' => $row['test_id'],
            'test_name' => $row['test_name']
        ];
    }
}

echo json_encode($tests);
?>
