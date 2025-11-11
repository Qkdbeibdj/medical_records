<?php
session_start();
include 'includes/db_connect.php';

// 🚫 Prevent unauthorized access
if (!isset($_SESSION['verified_user_id'])) {
    header("Location: forgot_password.php?error=unauthorized");
    exit();
}

// Initialize error array
$errors = ['new_password' => '', 'confirm_password' => ''];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $user_id = $_SESSION['verified_user_id'];

    // Validation
    if (empty($new_password)) $errors['new_password'] = "New password is required.";
    if (empty($confirm_password)) $errors['confirm_password'] = "Please confirm your new password.";

    if (!$errors['new_password'] && !$errors['confirm_password']) {
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = "Password must be at least 8 characters long.";
        } else {
            // Securely hash the password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password and clear OTP
            $update = $conn->prepare("UPDATE users SET password_hash=?, otp_code=NULL, otp_expires=NULL WHERE user_id=?");
            $update->bind_param("si", $hashed, $user_id);

            if ($update->execute()) {
                // Destroy sensitive session data
                unset($_SESSION['verified_user_id'], $_SESSION['reset_email']);
                session_regenerate_id(true);

                // Redirect with secure message
                header("Location: index.php?msg=reset_success");
                exit();
            } else {
                $errors['new_password'] = "An error occurred. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<style>
/* General */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1rem;
    color: #3e1760;
}

/* Container */
.reset-container {
    width: 100%;
    max-width: 400px;
    background: #fff;
    padding: 32px;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    text-align: center;
}
.reset-container h2 {
    font-size: 1.6rem;
    margin-bottom: 25px;
    color: #333;
}

/* Form */
.form-group {
    position: relative;
    margin-bottom: 22px;
}
.form-group input {
    width: 100%;
    padding: 12px 44px 12px 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s ease;
}
.form-group input:focus {
    border-color: #007BFF;
}
.form-group label {
    position: absolute;
    left: 12px;
    top: 12px;
    color: #888;
    font-size: 0.95rem;
    pointer-events: none;
    transition: 0.25s ease;
    background: #fff;
    padding: 0 3px;
}
.form-group input:focus + label,
.form-group input:not(:placeholder-shown) + label {
    top: -8px;
    left: 10px;
    font-size: 0.8rem;
    color: #007BFF;
}

/* SVG Icon */
.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    cursor: pointer;
    fill: #666;
    transition: fill 0.2s ease;
}
.toggle-password:hover {
    fill: #000;
}

/* Feedback */
.error-msg, .feedback-msg {
    font-size: 0.85rem;
    margin-top: 5px;
    text-align: left;
}
.error-msg { color: red; }
.feedback-msg { color: #333; }

/* Button */
button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 6px;
    background: #007BFF;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.25s ease;
}
button:hover {
    background: #0056b3;
}

/* Mobile */
@media (max-width: 480px) {
    .reset-container {
        width: 92%;
        padding: 26px;
    }
}
</style>
</head>
<body>
<div class="reset-container">
    <h2>Reset Password</h2>
    <form method="POST" novalidate>
        <!-- New Password -->
        <div class="form-group">
            <input type="password" id="new_password" name="new_password" required placeholder=" " />
            <label for="new_password">New Password</label>
            <svg class="toggle-password" onclick="togglePassword('new_password', this)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 4.5c-7.633 0-11.5 7.5-11.5 7.5s3.867 7.5 11.5 7.5 11.5-7.5 11.5-7.5S19.633 4.5 12 4.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>
            </svg>
            <?php if ($errors['new_password']): ?>
                <div class="error-msg"><?= htmlspecialchars($errors['new_password']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <input type="password" id="confirm_password" name="confirm_password" required placeholder=" " />
            <label for="confirm_password">Confirm Password</label>
            <svg class="toggle-password" onclick="togglePassword('confirm_password', this)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 4.5c-7.633 0-11.5 7.5-11.5 7.5s3.867 7.5 11.5 7.5 11.5-7.5 11.5-7.5S19.633 4.5 12 4.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>
            </svg>
            <?php if ($errors['confirm_password']): ?>
                <div class="error-msg"><?= htmlspecialchars($errors['confirm_password']) ?></div>
            <?php endif; ?>
            <div id="confirm-feedback" class="feedback-msg"></div>
        </div>

        <button type="submit">Update Password</button>
    </form>
</div>

<script>
function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
    const isVisible = input.type === "text";
    input.type = isVisible ? "password" : "text";
    icon.innerHTML = isVisible
        ? `<path d="M12 4.5c-7.633 0-11.5 7.5-11.5 7.5s3.867 7.5 11.5 7.5 11.5-7.5 11.5-7.5S19.633 4.5 12 4.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>`
        : `<path d="M2 4.27L3.28 3 21 20.72 19.73 22l-2.2-2.2c-1.53.73-3.24 1.2-5.53 1.2C5.347 21 1.5 13.5 1.5 13.5c.9-1.78 2.09-3.39 3.53-4.74L2 4.27zM12 7a5 5 0 0 1 5 5c0 .65-.13 1.26-.37 1.82L9.18 6.37C9.74 6.13 10.35 6 11 6h1z"/>`;
}

// Password match feedback
const newPass = document.getElementById('new_password');
const confirmPass = document.getElementById('confirm_password');
const feedback = document.getElementById('confirm-feedback');

confirmPass.addEventListener('input', () => {
    if (!confirmPass.value) {
        feedback.textContent = '';
    } else if (confirmPass.value === newPass.value) {
        feedback.textContent = 'Passwords match ✅';
        feedback.style.color = 'green';
    } else {
        feedback.textContent = 'Passwords do not match ❌';
        feedback.style.color = 'red';
    }
});
</script>
</body>
</html>
