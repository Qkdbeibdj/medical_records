<?php
// Database connection
require '../includes/db_connect.php';

$response = [];

// Fetch Ishihara plates from the database
$sql = "SELECT id, image_path FROM ishihara_questions ORDER BY id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'id' => $row['id'],
            'image_path' => $row['image_path'],
        ];
    }
} else {
    $response = ['error' => 'No plates found.'];
}

echo json_encode($response);
?>
