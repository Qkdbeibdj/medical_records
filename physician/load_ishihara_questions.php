<?php
require '../includes/db_connect.php';

$studentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;

$query = "SELECT id, image_path, correct_answer FROM ishihara_questions ORDER BY RAND() LIMIT 14";
$result = $conn->query($query);

$questions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['image_path'])) {
            $questions[] = [
                'id' => (int)$row['id'],
                'image_path' => $row['image_path'],
                'correct_answer' => $row['correct_answer']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($questions);
?>
