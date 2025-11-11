<?php
include '../includes/db_connect.php';
session_start();

$physician_id = $_SESSION['user_id'] ?? 0;

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Update request status to 'rejected'
    $stmt = $conn->prepare("UPDATE certificate_requests SET status = 'rejected' WHERE request_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        // Fetch student ID for logging
        $student_stmt = $conn->prepare("SELECT student_id FROM certificate_requests WHERE request_id = ?");
        $student_stmt->bind_param("i", $id);
        $student_stmt->execute();
        $student_stmt->bind_result($student_id);
        $student_stmt->fetch();
        $student_stmt->close();

        // Log the rejection
        $details = json_encode([
            'request_id'   => $id,
            'physician_id' => $physician_id,
            'reason'       => 'Rejected by physician'
        ]);

        $details = "Rejected: Certificate request rejected by physician\n" .
           date("M d, Y h:i A");  // current timestamp

        $log_stmt = $conn->prepare("
            INSERT INTO student_certificate_logs (student_id, action, details)
            VALUES (?, 'rejected', ?)
        ");
        $log_stmt->bind_param("is", $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();


        echo "Request rejected successfully.";

    } else {
        echo "Error rejecting request.";
    }

    $stmt->close();
}
$conn->close();
?>
