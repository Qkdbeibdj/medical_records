<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

try {
    // 1️⃣ Fetch the current hashed password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_hashed_password);
    $stmt->fetch();
    $stmt->close();

    // 2️⃣ Always update name and email
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    // 3️⃣ Handle password update if fields are filled
    if (!empty($old_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("All password fields are required to change password.");
        }

        // Verify old password
        if (!password_verify($old_password, $current_hashed_password)) {
            throw new Exception("Old password is incorrect.");
        }

        // Match and length checks
        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match.");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("New password must be at least 6 characters long.");
        }

        // Hash new password
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_hashed, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // 4️⃣ Log the profile update
    $action_type = 'profile_update';
    $description = "Updated profile information (name/email" . (!empty($new_password) ? " + password" : "") . ")";
    $log_stmt = $conn->prepare("INSERT INTO physician_activity_log (physician_id, action_type, description) VALUES (?, ?, ?)");
    $log_stmt->bind_param("iss", $user_id, $action_type, $description);
    $log_stmt->execute();
    $log_stmt->close();

    echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
    exit();

} catch (Exception $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='dashboard.php';</script>";
    exit();
}
?>
