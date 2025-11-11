<?php
include '../includes/db_connect.php';
session_start();

$physician_id = $_SESSION['user_id'] ?? 0;

if (isset($_POST['request_id'], $_POST['claim_datetime'])) {
    $request_id = intval($_POST['request_id']);
    $claim_datetime = $_POST['claim_datetime'];

    $action_date = date('Y-m-d H:i:s'); // when physician approved

    // Update certificate request with status and claim datetime
    $stmt = $conn->prepare("
        UPDATE certificate_requests
        SET status = 'approved', claim_datetime = ?
        WHERE request_id = ?
    ");
    $stmt->bind_param("si", $claim_datetime, $request_id);

    if ($stmt->execute()) {

        // Fetch student ID
        $student_stmt = $conn->prepare("SELECT student_id FROM certificate_requests WHERE request_id = ?");
        $student_stmt->bind_param("i", $request_id);
        $student_stmt->execute();
        $student_stmt->bind_result($student_id);
        $student_stmt->fetch();
        $student_stmt->close();

        // Log in student_certificate_logs
        $details = json_encode([
            'request_id'      => $request_id,
            'physician_id'    => $physician_id,
            'action_date'     => $action_date,
            'claim_date_time' => $claim_datetime,
            'reason'          => 'Approved by physician'
        ]);

        $claim_datetime = $_POST['claim_datetime'] ?? ''; // comes from AJAX / modal
        $claim_display = date("M d, Y \a\\t h:i A", strtotime($claim_datetime));

        $details = "Approved: Certificate approved. Scheduled claim date and time on $claim_display";

        $log_stmt = $conn->prepare("
            INSERT INTO student_certificate_logs (student_id, action, details)
            VALUES (?, 'approved', ?)
        ");
        $log_stmt->bind_param("is", $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();


        echo "Certificate approved. Student can claim on " . $claim_datetime;
    } else {
        echo "Error approving request.";
    }

    $stmt->close();
} else {
    echo "Missing required data.";
}

$conn->close();
?>
