<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if (!isset($_GET['test_id'])) {
    echo json_encode(['error' => 'Missing test_id parameter']);
    exit;
}

$test_id = intval($_GET['test_id']);
if ($test_id <= 0) {
    echo json_encode(['error' => 'Invalid test_id']);
    exit;
}

// Fetch students who have NOT taken the selected test
$sql = "
    SELECT s.student_id, s.name
    FROM students s
    WHERE s.student_id NOT IN (
        SELECT st.student_id
        FROM student_tests st
        WHERE st.test_id = ?
    )
    ORDER BY s.name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $test_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'student_id' => $row['student_id'],
        'name' => $row['name']
    ];
}

$stmt->close();
echo json_encode($students);
?>
