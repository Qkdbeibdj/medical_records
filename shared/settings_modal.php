<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$password_change_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!$user_id) {
        $password_change_msg = "<p class='text-red-600 text-sm'>User not logged in.</p>";
    } else {
        $query = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $password_change_msg = "<p class='text-red-600 text-sm'>User not found.</p>";
        } elseif (!password_verify($old_password, $user['password'])) {
            $password_change_msg = "<p class='text-red-600 text-sm'>Old password is incorrect.</p>";
        } elseif (strlen($new_password) < 8) {
            $password_change_msg = "<p class='text-red-600 text-sm'>New password must be at least 8 characters long.</p>";
        } elseif ($new_password !== $confirm_password) {
            $password_change_msg = "<p class='text-red-600 text-sm'>Passwords do not match.</p>";
        } else {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $update->bind_param("si", $hashed_new_password, $user_id);
            if ($update->execute()) {
                $password_change_msg = "<p class='text-green-600 text-sm'>Password updated successfully.</p>";
            } else {
                $password_change_msg = "<p class='text-red-600 text-sm'>Error updating password.</p>";
            }
        }
    }
}
?>

<!-- ✅ SETTINGS MODAL (shadcn/ui style) -->
<div id="settingsModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-50">
    <div class="bg-white rounded-lg w-full max-w-md p-6 relative">
        <button onclick="closeSettingsModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">
            <i class="bi bi-x-lg text-xl"></i>
        </button>

        <h2 class="text-xl font-semibold text-gray-800 mb-4">Change Password</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Old Password</label>
                <input type="password" name="old_password" class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="new_password" minlength="8" class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" minlength="8" class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </div>

            <?= $password_change_msg ?>

            <div class="pt-2">
                <button type="submit" name="change_password"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md font-medium transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openSettingsModal() {
    document.getElementById('settingsModal').classList.remove('hidden');
    document.getElementById('settingsModal').classList.add('flex');
}
function closeSettingsModal() {
    document.getElementById('settingsModal').classList.add('hidden');
    document.getElementById('settingsModal').classList.remove('flex');
}
window.addEventListener('click', function(e) {
    const modal = document.getElementById('settingsModal');
    if (e.target === modal) closeSettingsModal();
});
</script>
