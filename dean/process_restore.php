<?php
session_start();
include '../includes/db_connect.php';

if ($_SESSION['role'] !== 'dean') {
    header("Location: ../index.php");
    exit();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'medical_records';

if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
    $tmpFile = $_FILES['backup_file']['tmp_name'];
    $filename = $_FILES['backup_file']['name'];

    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
        $_SESSION['backup_message'] = "⚠️ Invalid file type. Please upload a .sql file.";
    } else {
        $command = "mysql --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name} < \"{$tmpFile}\"";
        $output = null;
        $return_var = null;
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            $_SESSION['backup_message'] = "✅ Database restored successfully from {$filename}.";
        } else {
            $_SESSION['backup_message'] = "❌ Restore failed. Please verify the SQL file or permissions.";
        }
    }
} else {
    $_SESSION['backup_message'] = "⚠️ No file selected or upload error.";
}

header("Location: backup_restore.php");
exit();
?>
