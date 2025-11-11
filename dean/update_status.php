<?php
session_start();
require '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dean') {
    http_response_code(403);
    exit('Unauthorized');
}

$student_id = intval($_POST['student_id']);
$status = $_POST['status'];

if (in_array($status, ['Active', 'Inactive'])) {
    $stmt = $conn->prepare("UPDATE students SET status = ? WHERE student_id = ?");
    $stmt->bind_param("si", $status, $student_id);
    $stmt->execute();
    echo "success";
} else {
    http_response_code(400);
    echo "Invalid status.";
}
?>
