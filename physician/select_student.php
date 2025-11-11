<?php
include '../includes/db_connect.php';

// Fetch all students
$stmt = $pdo->query("SELECT student_number, name FROM students");
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Student for Ishihara Test</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f3; text-align: center; padding: 20px; }
        select, button { padding: 10px 20px; margin-top: 10px; font-size: 16px; }
    </style>
</head>
<body>

<h2>Select a Student for the Ishihara Test</h2>

<!-- Dropdown to select a student -->
<select id="studentSelect">
    <option value="">-- Select a Student --</option>
    <?php foreach ($students as $student): ?>
        <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
    <?php endforeach; ?>
</select>

<br><br>
<!-- Button to start the Ishihara Test -->
<button onclick="openIshiharaTest()">Start Ishihara Test</button>

<script>
    function openIshiharaTest() {
        // Get the selected student ID
        const selectedStudent = document.getElementById("studentSelect").value;

        // If no student is selected, show an alert
        if (!selectedStudent) {
            alert("Please select a student first.");
            return;
        }

        // Redirect to the Ishihara test page with the selected student ID
        window.location.href = `start_ishihara.php?student_id=${selectedStudent}`;
    }
</script>

</body>
</html>
