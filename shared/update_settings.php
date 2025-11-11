<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

// Prepare the update query
if ($password) {
    $query = "UPDATE users SET name = ?, email = ?, password_hash = ? WHERE user_id = ?";
} else {
    $query = "UPDATE users SET name = ?, email = ? WHERE user_id = ?";
}

$stmt = $conn->prepare($query);

if ($password) {
    $stmt->bind_param("sssi", $name, $email, $password, $user_id);
} else {
    $stmt->bind_param("ssi", $name, $email, $user_id);
}

if ($stmt->execute()) {
    echo "<script>alert('Profile updated successfully!'); window.location.href='physician_dashboard.php';</script>";
} else {
    echo "Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
