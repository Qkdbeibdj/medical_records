<?php
include '../includes/db_connect.php';
session_start();

$physician_id = $_SESSION['user_id'] ?? 0;

if (!$physician_id) {
    die("⚠️ Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["certificate_file"])) {

    $student_id = intval($_POST['student_id']);
    $upload_dir = "../uploads/certificates/";

    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $original_name = $_FILES["certificate_file"]["name"];
    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed_types = ['docx', 'pdf'];

    if (!in_array($file_extension, $allowed_types)) {
        die("Error: Only .docx and .pdf files are allowed.");
    }

    // Generate unique file name
    $file_name = "medical_certificate_{$student_id}_" . time() . "." . $file_extension;
    $target_file = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES["certificate_file"]["tmp_name"], $target_file)) {
        die("Error uploading file.");
    }

    // Store relative path in DB
    $relative_path = "uploads/certificates/" . $file_name;

    // Ensure student has only one certificate row
    $stmt = $conn->prepare("
        INSERT INTO medical_certificate (student_id, certificate_file, status)
        VALUES (?, ?, 'Approved')
        ON DUPLICATE KEY UPDATE certificate_file = VALUES(certificate_file), status = 'Approved'
    ");
    $stmt->bind_param("is", $student_id, $relative_path);
    if (!$stmt->execute()) {
        die("Database error: " . $stmt->error);
    }
    $stmt->close();

    // Logging into physician_activity_log
    $description = "Uploaded certificate for student ID: $student_id, file: $file_name";

    $log_stmt = $conn->prepare("
        INSERT INTO physician_activity_log (physician_id, student_id, action_type, description)
        VALUES (?, ?, 'certificate', ?)
    ");
    $log_stmt->bind_param("iis", $physician_id, $student_id, $description);
    $log_stmt->execute();
    $log_stmt->close();

    // Redirect to dashboard with success message
    header("Location: dashboard.php?upload=success");
    exit();
} else {
    die("No certificate file uploaded.");
}
?>
