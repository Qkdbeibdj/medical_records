<?php
session_start();
include '../includes/db_connect.php';
header('Content-Type: application/json');

// ✅ Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION['student_id'];

/* ======================================================
   🔹 CHECK IF STUDENT ALREADY HAS A PENDING OR APPROVED REQUEST
====================================================== */
$stmt = $conn->prepare("
    SELECT status 
    FROM certificate_requests 
    WHERE student_id = ? 
    ORDER BY COALESCE(created_at, requested_at) DESC 
    LIMIT 1
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if ($existing && in_array(strtolower($existing['status']), ['pending', 'approved'])) {
    echo json_encode([
        'status' => 'already_requested',
        'message' => 'You already have a pending or approved certificate request.'
    ]);
    exit();
}

/* ======================================================
   🔹 HANDLE RECEIPT UPLOAD
====================================================== */
if (!isset($_FILES['receipt_file']) || $_FILES['receipt_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Please upload a valid receipt file.']);
    exit();
}

// Allowed file types
$allowed = ['jpg', 'jpeg', 'png', 'pdf'];
$ext = strtolower(pathinfo($_FILES['receipt_file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Allowed: JPG, PNG, PDF.']);
    exit();
}

$uploadDir = '../uploads/receipts/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$newFileName = 'receipt_' . $student_id . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (!move_uploaded_file($_FILES['receipt_file']['tmp_name'], $targetPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload receipt file.']);
    exit();
}

/* ======================================================
   🔹 INSERT NEW CERTIFICATE REQUEST
====================================================== */
$stmt = $conn->prepare("
    INSERT INTO certificate_requests (student_id, receipt_file, status, requested_at)
    VALUES (?, ?, 'pending', NOW())
");
$stmt->bind_param("is", $student_id, $targetPath);

if ($stmt->execute()) {
    // Log the action
    $log_stmt = $conn->prepare("
        INSERT INTO student_certificate_logs (student_id, action, details, created_at)
        VALUES (?, 'requested', 'Student submitted a new certificate request with receipt.', NOW())
    ");
    $log_stmt->bind_param("i", $student_id);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Your certificate request has been submitted successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error submitting request. Please try again.'
    ]);
}

$stmt->close();
$conn->close();
exit();
?>
