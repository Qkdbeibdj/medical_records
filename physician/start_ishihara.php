<?php
include '../includes/db_connect.php';
$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    die("Student ID required.");
}

// Check if test was already taken
$stmt = $pdo->prepare("SELECT COUNT(*) FROM student_ishihara_answers WHERE student_id = ?");
$stmt->execute([$student_id]);
if ($stmt->fetchColumn() > 0) {
    echo "<h3>Test already taken. Scores are shown below.</h3>";
    $summary = $pdo->prepare("SELECT COUNT(*) as total, SUM(is_correct) as correct FROM student_ishihara_answers WHERE student_id = ?");
    $summary->execute([$student_id]);
    $data = $summary->fetch();
    echo "<p>Total Questions: {$data['total']}</p>";
    echo "<p>Correct Answers: {$data['correct']}</p>";
    exit;
}

$plates = $pdo->query("SELECT * FROM ishihara_questions")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ishihara Test</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f3; text-align: center; padding: 20px; }
        .plate { margin: 20px auto; }
        img { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        input { padding: 10px; margin-top: 10px; border-radius: 10px; border: none; background: #e0e5ec; box-shadow: inset 4px 4px 10px #babecc, inset -4px -4px 10px #ffffff; }
        button { margin-top: 20px; padding: 10px 20px; border-radius: 10px; border: none; background: #28a745; color: white; cursor: pointer; }
    </style>
</head>
<body>
<h2>Ishihara Color Blindness Test</h2>
<form id="ishiharaForm" method="POST" action="submit_ishihara.php">
<input type="hidden" name="student_id" value="<?= $student_id ?>">
<div id="testContainer"></div>
<button type="submit">Submit Test</button>
</form>

<script>
const plates = <?php echo json_encode($plates); ?>;
const container = document.getElementById("testContainer");
let index = 0;

function showNextPlate() {
    if (index >= plates.length) {
        document.querySelector("button[type=submit]").style.display = "block";
        return;
    }
    const plate = plates[index];
    const div = document.createElement("div");
    div.className = "plate";
    div.innerHTML = `
        <img src="${plate.image_path}" width="200"><br>
        <input name="answers[${plate.id}]" placeholder="What number do you see?">
    `;
    container.appendChild(div);
    index++;
    setTimeout(showNextPlate, 1000); // 3-second delay per plate
}

document.querySelector("button[type=submit]").style.display = "none";
showNextPlate();
</script>
</body>
</html>

<?php
// submit_ishihara.php (server-side logic remains the same as in earlier message)
?>

<?php
// Optional: After test submission, you can generate a record into the certificate logic table if passed:
// e.g. INSERT INTO medical_certificate WHERE correct_answers >= threshold
?>
