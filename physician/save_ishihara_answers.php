<?php
include '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$studentId = $data['student_id'];
$answers = $data['answers'];

foreach ($answers as $a) {
    $questionId = $a['question_id'];
    $userAnswer = mysqli_real_escape_string($conn, $a['user_answer']);
    $isCorrect = $a['is_correct'];

    $query = "INSERT INTO student_ishihara_answers (student_id, question_id, user_answer, is_correct)
              VALUES ($studentId, $questionId, '$userAnswer', $isCorrect)";
    mysqli_query($conn, $query);
}

echo "Answers saved successfully.";
?>
