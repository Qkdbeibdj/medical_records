<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");

include '../includes/db_connect.php';

$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

if (!$studentId) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit();
}

$stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name, mc.issued_date FROM students s LEFT JOIN medical_certificate mc ON s.student_id = mc.student_id WHERE s.student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'name' => $row['full_name'],
        'issued_date' => $row['issued_date'] ?? null,
        'is_uploaded' => !empty($row['issued_date']) ? true : false
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No data found']);
}
$stmt->close();
$conn->close();
?>
