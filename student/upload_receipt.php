<?php
require_once '../includes/db_connect.php'; // Use your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt_file'])) {
    $student_id = $_POST['student_id'];

    // File upload directory
    $uploadDir = '../uploads/receipts/';
    $fileName = time() . '_' . basename($_FILES['receipt_file']['name']);
    $targetPath = $uploadDir . $fileName;

    // Create directory if not existing
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Allowed MIME types
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    if (in_array($_FILES['receipt_file']['type'], $allowedTypes)) {
        if (move_uploaded_file($_FILES['receipt_file']['tmp_name'], $targetPath)) {
            $relativePath = 'uploads/receipts/' . $fileName;

            // Check if there's already a record for this student
            $checkQuery = "SELECT receipt_file FROM medical_certificates WHERE student_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('s', $student_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existing = $checkResult->fetch_assoc();

            // If old file exists, delete it
            if ($existing && !empty($existing['receipt_file']) && file_exists('../' . $existing['receipt_file'])) {
                unlink('../' . $existing['receipt_file']);
            }

            // Update database with new receipt path
            $query = "UPDATE medical_certificate SET receipt_file = ? WHERE student_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $relativePath, $student_id);

            if ($stmt->execute()) {
                header("Location: dashboard.php?upload=success");
                exit;
            } else {
                echo "❌ Database update failed.";
            }
        } else {
            echo "❌ Failed to move uploaded file.";
        }
    } else {
        echo "⚠️ Invalid file type. Only JPG, PNG, or PDF allowed.";
    }
} else {
    echo "⚠️ No file uploaded.";
}
?>
