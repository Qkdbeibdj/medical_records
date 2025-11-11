<?php
session_start();
header('Content-Type: application/json');
include '../includes/db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$request_id = $input['request_id'] ?? null;
$action = $input['action'] ?? null;
$reason = $input['rejection_reason'] ?? null;

if (!$request_id || !in_array($action, ['approved','rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

if ($action === 'rejected' && !$reason) $reason = '';

$stmt = $conn->prepare("
    UPDATE certificate_requests
    SET status = ?, rejection_reason = ?
    WHERE request_id = ?
");
$stmt->bind_param('ssi', $action, $reason, $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}

$stmt->close();
