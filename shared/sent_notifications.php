<?php
require_once 'db_connect.php';

$student_id = $_POST['student_id'] ?? '';
$test_type = $_POST['test_type'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($student_id) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Get student's FCM token or other notification details
    $db = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "SELECT s.name, u.email FROM students s 
              JOIN users u ON s.student_id = u.student_id 
              WHERE s.student_id = :student_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // For demonstration, we'll just log the notification
        // In a real system, you would integrate with Firebase or another push notification service
        
        $log_message = "Notification sent to {$student['name']} ({$student['email']}): $message";
        file_put_contents('notification_log.txt', $log_message . PHP_EOL, FILE_APPEND);
        
        // You would include your Firebase integration here
        // Example:
        // $firebase = new Firebase();
        // $firebase->send($token, [
        //     'title' => 'Medical Test Update',
        //     'body' => $message
        // ]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>