<?php
include '../includes/db_connect.php';

$result = $conn->query("SELECT s.student_number, s.name, s.contact_number, 
                               m.status, m.certificate_file 
                        FROM students s 
                        LEFT JOIN medical_certificate m ON s.student_id = m.student_id");

if (!$result) {
    die("Query failed: " . $conn->error); // Debugging line (remove after testing)
}

$output = "";
while ($row = $result->fetch_assoc()) {
    // Ensure certificate file exists before displaying
    $certificateHtml = "";
    if (!empty($row['certificate_file']) && file_exists($row['certificate_file'])) {
        $fileUrl = htmlspecialchars($row['certificate_file']); // Ensure safe output
        $certificateHtml = '<a href="' . $fileUrl . '" target="_blank" class="btn btn-success">View</a> 
                            <a href="' . $fileUrl . '" download class="btn btn-primary">Download</a>';
    } else {
        $certificateHtml = '<span class="no-certificate text-danger">No Certificate Uploaded</span>';
    }

    $medicalStatus = !empty($row['certificate_file']) ? 'Approved' : 'Pending';

    $output .= '<tr>
                    <td>' . htmlspecialchars($row['student_number']) . '</td>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['contact_number']) . '</td>
                    <td>' . (!empty($row['status']) ? htmlspecialchars($row['status']) : 'Pending') . '</td>
                    <td>' . $certificateHtml . '</td>
                </tr>';
}

echo $output;
?>
