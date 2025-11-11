<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'dean') {
    header("Location: ../login.php");
    exit();
}

require '../includes/db_connect.php';

// Get student_id from URL
if (!isset($_GET['student_id'])) {
    header("Location: dean_home.php");
    exit();
}

$student_id = intval($_GET['student_id']);

// Fetch student info
$student_stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows === 0) {
    echo "Student not found.";
    exit();
}

$student = $student_result->fetch_assoc();

// Fetch student_tests WITH JOIN to medical_tests to get test_name
$tests_stmt = $conn->prepare("
    SELECT t.*, m.test_name 
    FROM student_tests t
    JOIN medical_tests m ON t.test_id = m.test_id
    WHERE t.student_id = ?
");
$tests_stmt->bind_param("i", $student_id);
$tests_stmt->execute();
$tests_result = $tests_stmt->get_result();

// Fetch Ishihara results
$ishihara_stmt = $conn->prepare("SELECT * FROM student_ishihara_results WHERE student_id = ?");
$ishihara_stmt->bind_param("i", $student_id);
$ishihara_stmt->execute();
$ishihara_result = $ishihara_stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];

    if ($new_status === 'Active' || $new_status === 'Inactive') {
        $update_stmt = $conn->prepare("UPDATE students SET status = ? WHERE student_id = ?");
        $update_stmt->bind_param("si", $new_status, $student_id);
        $update_stmt->execute();
        
        header("Location: dean_home.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Student - MERS</title>
    <link rel="stylesheet" href="../assets/css/neumorphism.css">
    <style>
        body { font-family: Arial, sans-serif; background: #e0e5ec; }
        .container { max-width: 900px; margin: auto; padding: 30px; }
        .card { background: #e0e5ec; border-radius: 20px; box-shadow: 8px 8px 15px #a3b1c6, -8px -8px 15px #ffffff; padding: 30px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; border-radius: 20px; overflow: hidden; box-shadow: 5px 5px 10px #a3b1c6, -5px -5px 10px #ffffff; }
        th, td { padding: 15px; text-align: center; background: #e0e5ec; }
        th { background: #d1d9e6; }
        .actions { margin-top: 20px; text-align: center; }
        button { padding: 10px 30px; font-size: 16px; border-radius: 10px; border: none; cursor: pointer; }
        .activate { background-color: #4CAF50; color: white; }
        .deactivate { background-color: #f44336; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>Evaluate Student</h1>
    <div class="card">
        <h2><?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_number']) ?>)</h2>
        <p>Course: <?= htmlspecialchars($student['course']) ?> | Year Level: <?= htmlspecialchars($student['year_level']) ?></p>
        <p>Current Status: <strong style="color: <?= $student['status'] == 'Active' ? 'green' : 'red'; ?>;"><?= $student['status'] ?></strong></p>
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
                <tr><td colspan="4">No general test results.</td></tr>
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

    <form method="POST" class="actions">
        <button type="submit" name="status" value="Active" class="activate">Set Active</button>
        <button type="submit" name="status" value="Inactive" class="deactivate">Set Inactive</button>
    </form>
</div>
</body>
</html>
