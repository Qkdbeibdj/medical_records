<?php
require '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int) $_POST['student_id'];
    $score = (int) $_POST['score'];
    $passed = (int) $_POST['passed'];

    if (!$studentId) {
        echo "Missing student ID.";
        exit;
    }

    // Create assessment string
    $assessment = $passed ? 'Passed' : 'Failed';

    // Optional: receive full answers via JSON from JS
    $answers = $_POST['answers'] ?? [];
    if (empty($answers) && isset($_POST['user_answers_json'])) {
        $answers = json_decode($_POST['user_answers_json'], true);
    }

    // If you want to store the full answer set:
    $encodedAnswers = json_encode($answers);

    $stmt = $conn->prepare("INSERT INTO student_ishihara_results (student_id, user_answer, score, assessment, submitted_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isis", $studentId, $encodedAnswers, $score, $assessment);

    if ($stmt->execute()) {
        echo "Result saved.";
    } else {
        echo "Error saving result: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
