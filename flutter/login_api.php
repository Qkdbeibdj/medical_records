<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

include '../db_connect.php';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!empty($email) && !empty($password)) {
    $stmt = $conn->prepare("SELECT user_id, name, role, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password_hash'])) {
            if ($row['role'] === 'student') {
                $studentId = $row['user_id'];

                $certStmt = $conn->prepare("SELECT issued_date FROM medical_certificate WHERE student_id = ? LIMIT 1");
                $certStmt->bind_param("i", $studentId);
                $certStmt->execute();
                $certResult = $certStmt->get_result();

                if ($certResult->num_rows > 0) {
                    $certData = $certResult->fetch_assoc();
                    $issuedDate = $certData['issued_date'];
                    $isUploaded = true;
                } else {
                    $issuedDate = null;
                    $isUploaded = false;
                }

                echo json_encode([
                    'success' => true,
                    'id' => $row['user_id'],
                    'name' => $row['name'],
                    'issued_date' => $issuedDate,
                    'certificate_uploaded' => $isUploaded,
                ]);

            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Access denied. Students only.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide both email and password.'
    ]);
}
?>
