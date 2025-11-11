<?php
session_start();
include 'includes/db_connect.php';

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Identify the student_id based on session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id']; // Student logged in
} elseif (isset($_SESSION['user_id'])) {
    // Fetch student_id if a physician/sto is logged in and viewing a specific student (optional logic)
    $user_id = $_SESSION['user_id'];
    if (isset($_GET['student_id'])) {
        $student_id = intval($_GET['student_id']);
    } else {
        echo "No student specified."; exit();
    }
} else {
    echo "User not logged in."; exit();
}

$test_id = $_GET['test_id'] ?? null;

if (!$test_id) {
    echo "No test ID provided."; exit();
}

// Check if test is Ishihara
if ($test_id === 'ishihara') {
    $stmt = $conn->prepare("SELECT score, assessment, submitted_at FROM student_ishihara_results WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        echo "<h3>Ishihara Test Results</h3>";
        echo "<p><strong>Score:</strong> " . htmlspecialchars($result['score']) . "</p>";
        echo "<p><strong>Assessment:</strong> " . htmlspecialchars($result['assessment']) . "</p>";
        echo "<p><strong>Submitted At:</strong> " . htmlspecialchars($result['submitted_at']) . "</p>";
    } else {
        echo "<p>No Ishihara test results available.</p>";
    }

} else {
    $query = "
        SELECT mt.test_name, st.assessment, st.created_at, 
               st.bp, st.hr, st.rr, st.o2_sat, st.temperature,
               st.subjective, st.past_history, st.family_history, st.physical_exam, st.blood_type, 
               st.lungs_findings, st.heart_findings, st.bones_findings, st.impression, st.hearing_result,
               st.thc_result, st.meth_result
        FROM student_tests st
        JOIN medical_tests mt ON st.test_id = mt.test_id
        WHERE st.student_id = ? AND st.test_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $test = $result->fetch_assoc();
    $stmt->close();

    if ($test) {
        echo "<h3>" . htmlspecialchars($test['test_name']) . "</h3>";
        echo "<p><strong>Test Taken On:</strong> " . htmlspecialchars($test['created_at']) . "</p>";
        echo "<p><strong>Assessment:</strong> " . htmlspecialchars(ucfirst($test['assessment'])) . "</p>";

        switch ((int)$test_id) {
            case 1:
                echo "<p><strong>BP:</strong> {$test['bp']}</p>";
                echo "<p><strong>HR:</strong> {$test['hr']}</p>";
                echo "<p><strong>RR:</strong> {$test['rr']}</p>";
                echo "<p><strong>O2 Sat:</strong> {$test['o2_sat']}</p>";
                echo "<p><strong>Temp:</strong> {$test['temperature']}</p>";
                echo "<p><strong>Subjective:</strong> {$test['subjective']}</p>";
                echo "<p><strong>Past History:</strong> {$test['past_history']}</p>";
                echo "<p><strong>Family History:</strong> {$test['family_history']}</p>";
                echo "<p><strong>Physical Exam:</strong> {$test['physical_exam']}</p>";
                break;
            case 2:
                echo "<p><strong>Blood Type:</strong> {$test['blood_type']}</p>";
                break;
            case 3:
                echo "<p><strong>Lungs:</strong> {$test['lungs_findings']}</p>";
                echo "<p><strong>Heart:</strong> {$test['heart_findings']}</p>";
                echo "<p><strong>Bones:</strong> {$test['bones_findings']}</p>";
                echo "<p><strong>Impression:</strong> {$test['impression']}</p>";
                break;
            case 4:
                echo "<p><strong>Hearing Result:</strong> {$test['hearing_result']}</p>";
                break;
            case 5:
                echo "<p><strong>THC Result:</strong> {$test['thc_result']}</p>";
                echo "<p><strong>Meth Result:</strong> {$test['meth_result']}</p>";
                break;
        }
    } else {
        echo "<p>No test data available.</p>";
    }
}
?>
