<?php
include '../includes/db_connect.php';

if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);

    // Get the certificate file path
    $query = "SELECT certificate_file FROM medical_certificate WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($file);
    $stmt->fetch();
    $stmt->close();

    // Build full file path and delete if exists
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/medical_records/' . $file;
    if ($file && file_exists($full_path)) {
        unlink($full_path);
    }

    // Delete from database
    $delete = "DELETE FROM medical_certificate WHERE student_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to the correct page
    header("Location: physician_dashboard.php?deleted=success");
    exit();
} else {
    header("Location: physician_dashboard.php?deleted=fail");
    exit();
}
?>
