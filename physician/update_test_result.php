<?php
require '../includes/db_connect.php';
session_start();

$physician_id = $_SESSION['user_id'] ?? 0;
$student_id = intval($_POST['student_id'] ?? 0);
$test_id = intval($_POST['test_id'] ?? 0);

// Validate IDs
if ($student_id <= 0 || $test_id <= 0) {
    echo 'error: invalid student_id or test_id';
    exit;
}

// Field mapping
$fields = [
    'blood_pressure' => 'bp',
    'heart_rate' => 'hr',
    'respiratory_rate' => 'rr',
    'oxygen_saturation' => 'o2_sat',
    'temperature' => 'temperature',
    'blood_type' => 'blood_type',
    'hearing_result' => 'hearing_result',
    'thc_result' => 'thc_result',
    'meth_result' => 'meth_result',
    'subjective_notes' => 'subjective',
    'past_history' => 'past_history',
    'family_history' => 'family_history',
    'physical_exam' => 'physical_exam',
    'assessment' => 'assessment',
    'lungs' => 'lungs_findings',
    'heart' => 'heart_findings',
    'bones' => 'bones_findings',
    'impression' => 'impression'
];

$updates = [];
$params = [];

foreach ($fields as $postKey => $dbField) {
    if (isset($_POST[$postKey])) {
        $updates[] = "$dbField = ?";
        $params[] = $_POST[$postKey];
    }
}

if (count($updates) > 0) {
    $sql = "UPDATE student_tests SET " . implode(', ', $updates) . " WHERE student_id = ? AND test_id = ?";
    $stmt = $conn->prepare($sql);

    $params[] = $student_id;
    $params[] = $test_id;
    $types = str_repeat('s', count($params) - 2) . 'ii';
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo 'success';

        // --- Logging ---
        $description = "Edited test result for student_id: $student_id, test_id: $test_id. Updated fields: " . implode(", ", array_keys($updates));

        $log_stmt = $conn->prepare("
            INSERT INTO physician_activity_log (physician_id, student_id, action_type, description)
            VALUES (?, ?, 'test_entry', ?)
        ");
        $log_stmt->bind_param("iis", $physician_id, $student_id, $description);
        $log_stmt->execute();
        $log_stmt->close();

    } else {
        echo 'error: ' . $stmt->error;
    }
} else {
    echo 'error: no fields to update';
}
?>
