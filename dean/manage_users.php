<?php
/******************************************************************
 *  manage_users.php
 *  Activate / deactivate student accounts
 ******************************************************************/

session_start();
require_once '../includes/db_connect.php';

/* ----------------------------------------------------------------
   1. AUTHORIZATION (dean or STO only)
-----------------------------------------------------------------*/
if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['dean', 'sto'], true)
) {
    header('Location: unauthorized.php');
    exit();
}

/* ----------------------------------------------------------------
   2. HANDLE TOGGLE REQUEST
-----------------------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['student_id'], $_POST['new_status'])
) {
    $studentId = (int) $_POST['student_id'];
    $newStatus = ($_POST['new_status'] === 'active') ? 'active' : 'inactive';

    $stmt = $conn->prepare("
        UPDATE students
        SET    status = ?
        WHERE  student_id = ?
    ");
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('si', $newStatus, $studentId);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid form resubmission
    header('Location: dean_dashboard.php?updated=1');

    exit();
}

/* ----------------------------------------------------------------
   3. FETCH STUDENT LIST
-----------------------------------------------------------------*/
$studentsStmt = $conn->query("
    SELECT student_id, student_number, name, status
    FROM students
    WHERE status = 'inactive'
    ORDER BY name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Neumorphism Style -->
    <style>
        body {
            background: #e0e5ec;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .neumo-box {
            background: #e0e5ec;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 10px 10px 20px #bebebe,
                        -10px -10px 20px #ffffff;
        }
        .btn-toggle {
            border: none;
            border-radius: 12px;
            padding: 8px 16px;
            background: #c3d1e1;
            box-shadow: 3px 3px 6px #b0b0b0,
                        -3px -3px 6px #ffffff;
            transition: background 0.18s ease-in-out;
        }
        .btn-toggle:hover {
            background: #a9c3dd;
        }
        tr > * {
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container my-4 neumo-box">
    <h3 class="text-center mb-4">
        <i class="bi bi-people-fill me-1"></i>Manage Students
    </h3>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success text-center py-2">
            Student status updated successfully.
        </div>
    <?php endif; ?>

    <!-- Search bar -->
    <div class="mb-3">
        <input type="text"
               id="searchInput"
               class="form-control"
               placeholder="Search by name, student number, or status…"
               onkeyup="filterRows()">
    </div>

    <!-- Student Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="studentsTable">
            <thead class="table-light">
                <tr>
                    <th>Student #</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $studentsStmt->fetch_assoc()): ?>
                <?php
                    $isActive = ($row['status'] === 'active');
                    $badgeCls = $isActive ? 'bg-success' : 'bg-danger';
                    $nextStat = $isActive ? 'inactive' : 'active';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_number']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <span class="badge <?= $badgeCls ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <form method="post"
                              style="display:inline;"
                              onsubmit="return confirm(
                                  'Are you sure you want to <?= $isActive ? 'DEACTIVATE' : 'ACTIVATE' ?> this account?'
                              );">
                            <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $nextStat ?>">
                            <button type="submit" class="btn-toggle">
                                <?= $isActive ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Client-side Search Script -->
<script>
function filterRows() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#studentsTable tbody tr');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
</script>

</body>
</html>
