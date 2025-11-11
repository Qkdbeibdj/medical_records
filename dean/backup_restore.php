<?php
// ==============================================
// DEAN - Backup and Restore Module
// ==============================================

session_start();
include '../includes/db_connect.php'; // adjust path if needed

// SECURITY: Allow only Dean role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dean') {
    header("Location: ../index.php");
    exit();
}

// Database credentials from db_connect.php
$db_host = 'localhost';
$db_user = 'root';          // change to your actual DB user
$db_pass = '';              // change to your actual DB password
$db_name = 'medical_records'; // your database name

$message = '';

// -------------------------------
// BACKUP PROCESS
// -------------------------------
if (isset($_POST['backup'])) {
    $backupDir = __DIR__ . '/backup/';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $filename = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $command = "mysqldump --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name} > {$filename}";
    system($command, $output);

    if ($output === 0) {
        $message = "<div class='alert alert-success'>✅ Backup created successfully! File saved as: <strong>" . basename($filename) . "</strong></div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Backup failed. Please check server permissions or database connection.</div>";
    }
}

// -------------------------------
// RESTORE PROCESS
// -------------------------------
if (isset($_POST['restore']) && isset($_FILES['sql_file'])) {
    $fileTmp = $_FILES['sql_file']['tmp_name'];
    $fileName = $_FILES['sql_file']['name'];

    if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'sql') {
        $message = "<div class='alert alert-warning'>⚠️ Invalid file type. Please upload a .sql file.</div>";
    } else {
        $command = "mysql --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name} < {$fileTmp}";
        system($command, $output);

        if ($output === 0) {
            $message = "<div class='alert alert-success'>✅ Database restored successfully from <strong>{$fileName}</strong>.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Restore failed. Please verify the SQL file integrity.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backup & Restore | Dean Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f6fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-width: 700px;
        }
        h2 {
            color: #3e1760;
            margin-bottom: 20px;
        }
        .btn {
            border-radius: 8px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🗄️ Backup & Restore Database</h2>
    <a href="dean_dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
    <p class="text-muted">Only the Dean can perform these operations. Please proceed carefully.</p>

    <?= $message; ?>

    <div class="mt-4">
        <form method="POST">
            <button type="submit" name="backup" class="btn btn-success w-100 mb-3">💾 Backup Database</button>
        </form>

        <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('⚠️ Restoring will overwrite existing data. Continue?');">
            <label class="form-label">Select SQL file to restore:</label>
            <input type="file" name="sql_file" accept=".sql" class="form-control mb-3" required>
            <button type="submit" name="restore" class="btn btn-danger w-100">♻️ Restore Database</button>
        </form>
    </div>

    <hr class="my-4">

    <h5>📂 Available Backups</h5>
    <ul class="list-group">
        <?php
        $files = glob(__DIR__ . '/backup/*.sql');
        if ($files) {
            foreach (array_reverse($files) as $file) {
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        " . basename($file) . "
                        <a href='backup/" . basename($file) . "' class='btn btn-sm btn-outline-primary' download>Download</a>
                      </li>";
            }
        } else {
            echo "<li class='list-group-item text-muted'>No backups found.</li>";
        }
        ?>
    </ul>
</div>


</body>
</html>
