<script>
function logPhysicianActivity($conn, $physician_id, $student_id = null, $action_type, $description) {
    $stmt = $conn->prepare("
        INSERT INTO physician_activity_log (physician_id, student_id, action_type, description)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $physician_id, $student_id, $action_type, $description);
    $stmt->execute();
    $stmt->close();
}
</script>