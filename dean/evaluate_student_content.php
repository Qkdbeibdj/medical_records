<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dean') {
    exit("Unauthorized");
}

require '../includes/db_connect.php';

if (!isset($_GET['student_id'])) {
    exit("No student selected.");
}

$student_id = intval($_GET['student_id']);

// Fetch student info
$student_stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows === 0) {
    exit("Student not found.");
}

$student = $student_result->fetch_assoc();

// Fetch student_tests
$tests_stmt = $conn->prepare("
    SELECT t.*, m.test_name 
    FROM student_tests t
    JOIN medical_tests m ON t.test_id = m.test_id
    WHERE t.student_id = ?
");
$tests_stmt->bind_param("i", $student_id);
$tests_stmt->execute();
$tests_result = $tests_stmt->get_result();

// Fetch Ishihara
$ishihara_stmt = $conn->prepare("SELECT * FROM student_ishihara_results WHERE student_id = ?");
$ishihara_stmt->bind_param("i", $student_id);
$ishihara_stmt->execute();
$ishihara_result = $ishihara_stmt->get_result();
?>

<div class="card">
    <h2><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_number']) ?>)</h2>
    <p>Course: <?= htmlspecialchars($student['course']) ?> | Year Level: <?= htmlspecialchars($student['year_level']) ?></p>
    <p>Status: <strong style="color: <?= $student['status'] == 'Active' ? 'green' : 'red'; ?>;"><?= $student['status'] ?></strong></p>
</div>

<div class="card">
    <h3>Medical Test Results</h3>
    <table>
        <thead>
            <tr>
                <th>Test Type</th>
                <th>Date</th>
                <th>Assessment</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($tests_result->num_rows > 0): ?>
            <?php while ($test = $tests_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($test['test_name']) ?></td>
                <td><?= htmlspecialchars($test['created_at']) ?></td>
                <td style="color: <?= $test['assessment'] == 'Failed' ? 'red' : ($test['assessment'] == 'Conditional' ? 'orange' : 'green'); ?>;">
                    <?= htmlspecialchars($test['assessment']) ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No general test results.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Ishihara Test Results</h3>
    <table>
        <thead>
            <tr>
                <th>Score</th>
                <th>Interpretation</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($ishihara_result->num_rows > 0): ?>
            <?php while ($ish = $ishihara_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($ish['score']) ?></td>
                <td style="color: <?= $ish['score'] < 10 ? 'red' : 'green'; ?>">
                    <?= $ish['score'] < 10 ? 'Failed (Color Deficiency)' : 'Passed' ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">No Ishihara test results.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<form id="statusForm">
    <input type="hidden" name="student_id" value="<?= $student_id ?>">
    <div style="text-align:center; margin-top: 20px;">
        <button type="submit" name="status" value="Inactive" class="deactivate" style="padding: 10px 30px; font-size: 16px; border-radius: 10px; border: none; background-color: #f44336; color: white; cursor: pointer;">Set Inactive</button>
    </div>
</form>
<div id="statusMessage" style="text-align:center; margin-top:10px; color:green;"></div>

<script>
// Attach submit event directly inside the loaded content
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('update_student_status.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        document.getElementById('statusMessage').innerText = "Status updated successfully!";
    })
    .catch(err => {
        document.getElementById('statusMessage').innerText = "Error updating status.";
    });
});
</script>
