<?php
require '../includes/db_connect.php';
header('Content-Type: application/json');

$studentId = $_GET['student_id'] ?? null;
$testId = $_GET['test_id'] ?? null;

if ($studentId && $testId) {
    $stmt = $conn->prepare("SELECT * FROM student_tests WHERE student_id = ? AND test_id = ?");
    $stmt->bind_param("ii", $studentId, $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}
?>
