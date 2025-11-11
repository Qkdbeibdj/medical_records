<?php
include '../includes/db_connect.php';

$course = $_GET['course'] ?? '';
$search = $_GET['q'] ?? '';
$search = $conn->real_escape_string($search);

$query = "
    SELECT 
        s.student_id, s.student_number, s.name, s.year_level, s.course, s.sex, s.contact_number,
        mt.test_name, st.assessment, st.test_id
    FROM students s
    LEFT JOIN student_tests st ON s.student_id = st.student_id
    LEFT JOIN medical_tests mt ON st.test_id = mt.test_id
    WHERE 1
";

if($course) $query .= " AND UPPER(s.course)= '".strtoupper($course)."'";
if($search) $query .= " AND (s.student_number LIKE '%$search%' OR s.name LIKE '%$search%')";

$query .= " ORDER BY s.student_number ASC, st.test_id DESC";

$result = $conn->query($query);

// Group students
$students = [];
while($row = $result->fetch_assoc()) {
    $sid = $row['student_id'];
    if(!isset($students[$sid])) $students[$sid] = ['info'=>$row, 'tests'=>[]];
    if($row['test_id']) $students[$sid]['tests'][] = $row;
}

// Helper to split name
function getFirstAndLastName($fullName) {
    $parts = preg_split('/\s+/', trim($fullName));
    return ['first'=>$parts[0] ?? '', 'last'=>$parts[count($parts)-1] ?? ''];
}

// Render table
echo "<table class='table'><thead>
<tr>
<th>Student Number</th>
<th>First Name</th>
<th>Last Name</th>
<th>Year Level</th>
<th>Course</th>
<th>Sex</th>
<th>Contact Number</th>
</tr></thead><tbody>";

foreach($students as $sid=>$data){
    $student = $data['info'];
    $nameParts = getFirstAndLastName($student['name']);

    echo "<tr>
        <td>{$student['student_number']}</td>
        <td>".htmlspecialchars($nameParts['first'])."</td>
        <td>".htmlspecialchars($nameParts['last'])."</td>
        <td>{$student['year_level']}</td>
        <td>{$student['course']}</td>
        <td>{$student['sex']}</td>
        <td>{$student['contact_number']}</td>
    </tr>";

    foreach($data['tests'] as $index=>$test){
        $detailId = "detail-{$sid}-{$index}";
        $assessmentClass = strtolower(trim($test['assessment'] ?? ''));
        $statusClass = $assessmentClass==='passed'?'status-passed':($assessmentClass==='failed'?'status-failed':'status-conditional');

        echo "<tr class='test-links'><td colspan='7'>
            <button class='test-toggle $statusClass' data-detail-id='{$detailId}'>{$test['test_name']}</button>
        </td></tr>";

        echo "<tr id='{$detailId}' class='test-detail-row' style='display:none; background:#f1f1f1'><td colspan='7'>";
        echo "<strong>Assessment:</strong> ".htmlspecialchars($test['assessment'] ?? '')."<br>";
        echo "</td></tr>";
    }
}

echo "</tbody></table>";
?>
