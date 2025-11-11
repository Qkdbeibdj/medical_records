<?php
include '../includes/db_connect.php';

// Safely check if 'assessment' exists in the GET array
$assessmentFilter = isset($_GET['assessment']) ? $_GET['assessment'] : '';

// Query to join the necessary tables to get the test names and assessments
$query = "SELECT s.student_number, s.name, mt.test_name, st.assessment, s.student_id
          FROM student_tests st
          JOIN students s ON st.student_id = s.student_id
          JOIN medical_tests mt ON st.test_id = mt.test_id";

// Apply the assessment filter only if it's not empty
if ($assessmentFilter !== '') {
    $query .= " WHERE st.assessment = '" . $conn->real_escape_string($assessmentFilter) . "'";
}

$query .= " ORDER BY s.student_number, st.created_at DESC";

$result = $conn->query($query);
$students = [];

// Organize tests by student
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $student_number = $row['student_number'];
        $name = $row['name'];
        $test_name = $row['test_name'];
        $assessment = $row['assessment'];

        // Group tests by student number
        if (!isset($students[$student_number])) {
            $students[$student_number] = [
                'student_number' => $student_number,
                'name' => $name,
                'test_names' => [],
                'student_id' => $row['student_id']
            ];
        }

        // Add the test names for the student
        $students[$student_number]['test_names'][] = $test_name;
    }

    // Return the student data as a JSON response
    echo json_encode(array_values($students));
} else {
    echo json_encode([]); // Return empty array if no records found
}

?>
