<?php
include '../includes/db_connect.php';

// Use the correct table name
$sql = "SELECT * FROM physician_activity_log ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>
            <th>Physician ID</th>
            <th>Student ID</th>
            <th>Action Type</th>
            <th>Description</th>
            <th>Time</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['physician_id']) . "</td>
                <td>" . htmlspecialchars($row['student_id'] ?? '-') . "</td>
                <td>" . htmlspecialchars($row['action_type']) . "</td>
                <td>" . htmlspecialchars($row['description']) . "</td>
                <td>" . $row['created_at'] . "</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No activity logs found.";
}
?>
