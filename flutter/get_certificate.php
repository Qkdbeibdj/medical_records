<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");

include '../db_connect.php';

$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

if (!$studentId) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit();
}

$stmt = $conn->prepare("
    SELECT s.name, mc.issued_date
    FROM students s
    LEFT JOIN medical_certificate mc ON s.student_id = mc.student_id
    WHERE s.student_id = ?
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $status = !empty($row['issued_date']) ? 'Issued' : 'Pending';

    echo json_encode([
        'success' => true,
        'student' => [
            'name' => $row['name'],
            'issued_date' => $row['issued_date'] ?? 'Not yet issued',
            'status' => $status
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
}

$stmt->close();
$conn->close();
?>
