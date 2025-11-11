<?php
session_start();
include '../includes/db_connect.php';

if ($_SESSION['role'] !== 'dean') {
    header("Location: ../index.php");
    exit();
}

$db_host = 'localhost';
$db_user = 'root';           // your MySQL username
$db_pass = '';               // your MySQL password
$db_name = 'medical_records'; // your DB name

$backupDir = __DIR__ . '/backup/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$filename = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// Run mysqldump to export the entire database
$command = "mysqldump --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name} > \"{$filename}\"";
$output = null;
$return_var = null;
exec($command, $output, $return_var);

if ($return_var === 0) {
    $_SESSION['backup_message'] = "✅ Backup successful! File saved as: " . basename($filename);
} else {
    $_SESSION['backup_message'] = "❌ Backup failed. Please check database access or permissions.";
}

header("Location: backup_restore.php");
exit();
?>
