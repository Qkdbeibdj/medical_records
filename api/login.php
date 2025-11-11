<?php
include('../db_connect.php');
$data = json_decode(file_get_contents("php://input"), true);

$student_number = $data['student_number'];
$query = $conn->prepare("SELECT * FROM students WHERE student_number = ?");
$query->bind_param("s", $student_number);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $student]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Student Number"]);
}
?>
