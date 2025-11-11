<?php
require '../includes/db_connect.php';

$student_id = intval($_GET['student_id'] ?? 0);
$available_tests = [];

if ($student_id > 0) {
    $taken_result = $conn->query("SELECT test_id FROM student_tests WHERE student_id = $student_id");
    $taken_tests = [];

    while ($row = $taken_result->fetch_assoc()) {
        // Ensure we only get numeric test IDs
        if (is_numeric($row['test_id'])) {
            $taken_tests[] = intval($row['test_id']);
        }
    }

    // If no tests taken, show all
    if (!empty($taken_tests)) {
        $taken_ids = implode(',', $taken_tests);
        $sql = "SELECT test_id, test_name FROM medical_tests WHERE test_id NOT IN ($taken_ids)";
    } else {
        $sql = "SELECT test_id, test_name FROM medical_tests";
    }

    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $available_tests[] = $row;
        }
    } else {
        // Log SQL error for debugging
        error_log("SQL Error: " . $conn->error);
    }
}

header('Content-Type: application/json');
echo json_encode($available_tests);
?>
