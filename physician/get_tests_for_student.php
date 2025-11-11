<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if (!isset($_GET['student_id'])) {
    echo json_encode(['error' => 'Missing student_id parameter']);
    exit;
}

$student_id = intval($_GET['student_id']);
if ($student_id <= 0) {
    echo json_encode(['error' => 'Invalid student_id']);
    exit;
}

$stmt = $conn->prepare("
    SELECT mt.test_id, mt.test_name
    FROM medical_tests mt
    WHERE NOT EXISTS (
        SELECT 1 
        FROM student_tests st
        WHERE st.student_id = ? 
          AND st.test_id = mt.test_id
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM student_notifications sn
        WHERE sn.student_id = ?
          AND sn.test_type = mt.test_name
    )
    ORDER BY mt.test_name
");
$stmt->bind_param('ii', $student_id, $student_id);

$stmt->execute();
$result = $stmt->get_result();

$tests = [];
while ($row = $result->fetch_assoc()) {
    $tests[] = [
        'test_id' => $row['test_id'],
        'test_name' => $row['test_name']
    ];
}

$stmt->close();
echo json_encode($tests);
exit;