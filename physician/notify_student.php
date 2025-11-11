<?php
// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../includes/db_connect.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$physician_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $physician_id) {
    $student_id = intval($_POST['student_id']);
    $test_id = $_POST['test_type'] ?? '';
    $test_datetime = $_POST['test_date'] ?? '';
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';

    // --- Determine test name ---
    if (is_numeric($test_id)) {
        $stmt = $conn->prepare("SELECT test_name FROM medical_tests WHERE test_id = ?");
        $stmt->bind_param("i", $test_id);
        $stmt->execute();
        $stmt->bind_result($test_name);
        $stmt->fetch();
        $stmt->close();

        if (empty($test_name)) {
            echo "❌ Invalid test selected.";
            exit();
        }
    } elseif (strtolower($test_id) === 'ishihara') {
        $test_name = 'Ishihara Test';
    } else {
        echo "❌ Invalid test type.";
        exit();
    }

    // --- Check duplicate notification ---
    $stmt = $conn->prepare("SELECT 1 FROM student_notifications WHERE student_id = ? AND test_type = ?");
    $stmt->bind_param("is", $student_id, $test_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: homepage.php?already_scheduled=1");
        exit();
    }
    $stmt->close();

    // --- Insert notification into DB ---
    $stmt = $conn->prepare("
        INSERT INTO student_notifications 
            (student_id, physician_id, test_type, test_datetime, deadline, physician_user_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisssi", 
        $student_id, 
        $physician_id, 
        $test_name, 
        $test_datetime, 
        $deadline, 
        $physician_id
    );

    if ($stmt->execute()) {
        $stmt->close();

        // --- Fetch student email and name ---
        $stmt = $conn->prepare("SELECT email, name FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->bind_result($student_email_raw, $student_name_raw);
        $stmt->fetch();
        $stmt->close();

        $student_email = trim($student_email_raw);
        $student_name = trim($student_name_raw);

        if (empty($student_email)) {
            echo "❌ Email is empty. student_id: $student_id";
            exit();
        }

        // --- Send email via PHPMailer ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ballescaian123@gmail.com';
            $mail->Password = 'fmlnrqqnjlddtjjv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('ballescaian123@gmail.com', 'Vital Care Clinic');
            $mail->addAddress($student_email, $student_name);
            $mail->addReplyTo('noreply@philcst.edu.ph', 'Do Not Reply');

            $mail->Subject = "📢 {$test_name} Scheduled on " . date('F j, Y', strtotime($test_datetime));
            $mail->isHTML(true);
            $mail->Body = "
                <p>Hi <strong>{$student_name}</strong>,</p>
                <p>You are scheduled for <strong>{$test_name}</strong> on 
                <strong>" . date('F j, Y \\a\\t g:i A', strtotime($test_datetime)) . "</strong>.</p>
                " . (!empty($deadline) ? "<p><strong>Deadline:</strong> {$deadline}</p>" : "") . "
                <p>{$message}</p>
                <br><p>— PhilCST Medical Unit</p>";
            $mail->AltBody = "You are scheduled for {$test_name} on " . date('F j, Y g:i A', strtotime($test_datetime)) .
                             (!empty($deadline) ? "\nDeadline: {$deadline}" : '') .
                             "\n{$message}\n\n— PhilCST Medical Unit";

            $mail->send();
        } catch (Exception $e) {
            echo "❌ Email sending failed: " . $mail->ErrorInfo;
            exit();
        }

        // --- Done, redirect ---
        header("Location: homepage.php?success=1");
        exit();
    } else {
        echo "❌ Failed to insert notification: " . $stmt->error;
        exit();
    }
} else {
    echo "❌ Unauthorized or invalid request.";
    exit();
}
?>
