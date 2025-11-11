<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming the test data includes the student ID and test ID
    $studentId = $_POST['student_id'];
    $testId = $_POST['medical_test'];
    $testResult = $_POST['test_result'];  // Example result field

    $query = "INSERT INTO student_tests (student_id, test_id, test_result) VALUES ('$studentId', '$testId', '$testResult')";
    if ($conn->query($query) === TRUE) {
        echo json_encode(['status' => 'success', 'studentId' => $studentId]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save test data.']);
    }
}
?>
