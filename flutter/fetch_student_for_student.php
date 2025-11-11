<?php
include '../db_connect.php';
header('Content-Type: application/json');

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit;
}

$result = $conn->query("SELECT s.student_number, s.name, m.issued_date, m.status, m.certificate_file 
                        FROM students s 
                        LEFT JOIN medical_certificate m ON s.student_id = m.student_id
                        WHERE s.student_id = $student_id");

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}

if ($row = $result->fetch_assoc()) {
    $certificateFolder = '/medical_records/uploads/certificates/';
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $certificateFolder . $row['certificate_file'];

    $certificateHtml = null;

    if (!empty($row['certificate_file']) && file_exists($absolutePath)) {
        $fileUrl = 'http://' . $_SERVER['HTTP_HOST'] . $certificateFolder . $row['certificate_file'];
        $certificateHtml = [
            'view_url' => $fileUrl,
            'download_url' => $fileUrl
        ];
    }

    $studentData = [
        'student_number' => $row['student_number'],
        'name' => $row['name'],
        'issued_date' => $row['issued_date'] ?: 'Not issued',
        'status' => $row['status'] ?: 'Pending',
        'certificate' => $certificateHtml
    ];

    echo json_encode(['success' => true, 'student' => $studentData]);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
}
?>
