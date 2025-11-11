<?php
require '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['student_number'])) {
        $student_number = $_POST['student_number'];

        // Fetch the student ID and associated user ID
        $stmt_check = $conn->prepare("SELECT student_id, user_id FROM students WHERE student_number = ?");
        $stmt_check->bind_param("s", $student_number);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $student_id = $student['student_id'];
            $user_id = $student['user_id'];
            $stmt_check->close();

            // Start transaction
            $conn->begin_transaction();

            try {
                // Delete from all related tables using student_id
                $tables = [
                    "student_notifications",
                    "student_tests",
                    "student_ishihara_results",
                    "medical_certificate"
               ];

                foreach ($tables as $table) {
                    $stmt = $conn->prepare("DELETE FROM $table WHERE student_id = ?");
                    $stmt->bind_param("i", $student_id);
                    $stmt->execute();
                    $stmt->close();
                }

                // Delete from users table using user_id
                $stmt_user = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt_user->bind_param("i", $user_id);
                $stmt_user->execute();
                $stmt_user->close();

                // Delete from students table
                $stmt_student = $conn->prepare("DELETE FROM students WHERE student_id = ?");
                $stmt_student->bind_param("i", $student_id);
                $stmt_student->execute();
                $stmt_student->close();

                // Commit transaction
                $conn->commit();

                echo "<script>alert('Student and all related data removed successfully!'); window.location.href='dean_dashboard.php';</script>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<script>alert('Error during deletion: " . $e->getMessage() . "'); window.location.href='dean_dashboard.php';</script>";
            }

        } else {
            echo "<script>alert('Student not found!'); window.location.href='dean_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid request!'); window.location.href='dean_dashboard.php';</script>";
    }
} else {
    header("Location: dean_dashboard.php");
    exit();
}
?>
