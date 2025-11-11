<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_GET['student_id']) || !isset($_SESSION['student_id']) || $_GET['student_id'] != $_SESSION['student_id']) {
    echo "Unauthorized access.";
    exit;
}

$student_id = $_GET['student_id'];

$stmt = $conn->prepare("SELECT certificate_file FROM medical_certificate WHERE student_id = ? AND status = 'approved'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($file_path);
$stmt->fetch();
$stmt->close();

if (!$file_path || !file_exists($file_path)) {
    echo "Certificate not available or file missing.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Certificate</title>
</head>
<body>
    <h2>Your Medical Certificate</h2>
    <embed src="<?= htmlspecialchars($file_path) ?>" type="application/pdf" width="100%" height="600px">
</body>
</html>
