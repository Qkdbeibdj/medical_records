<?php
// Include database connection
include '../includes/db_connect.php';

// Set header to return JSON response always
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_test_data') {

    // Validate required fields
    if (empty($_POST['student_id']) || empty($_POST['test_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing student_id or test_id']);
        exit;
    }

    $student_id = intval($_POST['student_id']);
    $test_id = intval($_POST['test_id']);

    // Define test fields based on test_id
    $test_data = [];

    switch ($test_id) {
        case 1: // General Check-up
            $past_history = isset($_POST['past_history']) ? implode(', ', $_POST['past_history']) : '';
            $past_history_others = $_POST['past_history_others'] ?? '';
            $family_history = isset($_POST['family_history']) ? implode(', ', $_POST['family_history']) : '';
            $family_history_others = $_POST['family_history_others'] ?? '';
            $physical_exam = isset($_POST['physical_exam']) ? implode(', ', $_POST['physical_exam']) : '';
            $physical_exam_others = $_POST['physical_exam_others'] ?? '';

            $test_data = [
                'bp' => $_POST['bp'] ?? '',
                'hr' => $_POST['hr'] ?? '',
                'rr' => $_POST['rr'] ?? '',
                'o2_sat' => $_POST['o2_sat'] ?? '',
                'temperature' => $_POST['temperature'] ?? '',
                'subjective' => $_POST['subjective'] ?? '',
                'past_history' => trim($past_history . ' | ' . $past_history_others),
                'family_history' => trim($family_history . ' | ' . $family_history_others),
                'physical_exam' => trim($physical_exam . ' | ' . $physical_exam_others),
                'assessment' => $_POST['assessment'] ?? ''
            ];
            break;


        case 2: // Blood Typing
            $test_data = [
                'blood_type' => $_POST['blood_type'] ?? '',
                'assessment' => $_POST['assessment'] ?? ''
            ];
            break;

        case 3: // Chest X-ray
            $test_data = [
                'lungs_findings' => $_POST['lungs_findings'] ?? '',
                'heart_findings' => $_POST['heart_findings'] ?? '',
                'bones_findings' => $_POST['bones_findings'] ?? '',
                'impression' => $_POST['impression'] ?? '',
                'assessment' => $_POST['assessment'] ?? ''
            ];
            break;

        case 4: // Basic Hearing Screening
            $test_data = [
                'hearing_result' => $_POST['hearing_result'] ?? '',
                'assessment' => $_POST['assessment'] ?? ''
            ];
            break;

        case 5: // Drug Test
            $test_data = [
                'thc_result' => $_POST['thc_result'] ?? '',
                'meth_result' => $_POST['meth_result'] ?? '',
                'assessment' => $_POST['assessment'] ?? ''
            ];
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown test_id']);
            exit;
    }

    try {
        // Prepare the dynamic SQL for student_tests table
        $columns = implode(", ", array_merge(['student_id', 'test_id'], array_keys($test_data)));
        $placeholders = implode(", ", array_fill(0, count($test_data) + 2, '?'));
        $types = str_repeat('i', 2) . str_repeat('s', count($test_data)); // 2 integers + N strings

        file_put_contents('sql_debug_log.txt', "SQL: INSERT INTO student_tests ($columns) VALUES ($placeholders)\nValues: " . print_r($values, true), FILE_APPEND);


        $stmt = $conn->prepare("INSERT INTO student_tests ($columns) VALUES ($placeholders)");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind values dynamically
        $values = array_merge([$student_id, $test_id], array_values($test_data));
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ' - ERROR: ' . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}


} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
