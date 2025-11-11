<?php
session_start();
include 'includes/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = trim($_POST['otp']);
    $email = $_SESSION['reset_email'] ?? '';

    if (!empty($otp) && !empty($email)) {
        $stmt = $conn->prepare("SELECT user_id, otp_code, otp_expires FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['otp_code'] == $otp && strtotime($row['otp_expires']) > time()) {
                $_SESSION['verified_user_id'] = $row['user_id'];
                header("Location: reset_password.php");
                exit;
            } else {
                $message = "Invalid or expired OTP.";
            }
        } else {
            $message = "User not found.";
        }
    } else {
        $message = "Please enter the OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        /* =========================
   VERIFY OTP PAGE
   ========================= */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1rem;
    color: #3e1760;
}

/* CONTAINER */
.otp-container {
    width: 100%;
    max-width: 400px;
    min-width: 300px;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    text-align: center;
}

/* HEADER */
.otp-container h2 {
    font-size: 1.5rem;
    margin-bottom: 20px;
    color: #333;
}

/* FORM */
form {
    display: flex;
    flex-direction: column;
}

.form-group {
    position: relative;
    margin-bottom: 20px;
    width: 100%;
}

.form-group input {
    width: 100%;
    padding: 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #f9f9f9;
    outline: none;
}

.form-group label {
    position: absolute;
    left: 12px;
    top: 12px;
    color: #888;
    pointer-events: none;
    transition: 0.3s ease;
    font-size: 0.95rem;
}

.form-group input:focus + label,
.form-group input:not(:placeholder-shown) + label {
    top: -8px;
    left: 10px;
    font-size: 0.8rem;
    color: #007BFF;
    background: #fff;
    padding: 0 4px;
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 6px;
    background: #007BFF;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background: #0056b3;
}

/* ERROR / FEEDBACK */
.message {
    color: red;
    font-size: 0.95rem;
    margin-top: 15px;
    text-align: center;
}

/* LINKS */
p a {
    color: #007BFF;
    text-decoration: none;
}

p a:hover {
    text-decoration: underline;
}

/* =========================
   MOBILE VIEW
   ========================= */
@media (max-width: 480px) {
    .otp-container {
        width: 90%;
        padding: 25px;
    }

    h2 {
        font-size: 1.6rem;
        margin-bottom: 15px;
    }

    input, button {
        width: 100%;
        max-width: 100%;
        font-size: 1.2rem;
        padding: 12px;
    }

    label {
        font-size: 0.9rem;
    }

    .message {
        font-size: 0.9rem;
    }
}

/* VERY SMALL PHONES */
@media (max-width: 360px) {
    .otp-container {
        padding: 20px;
    }

    h2 {
        font-size: 1.4rem;
    }

    input, button {
        font-size: 1.1rem;
        padding: 10px;
    }

    label {
        font-size: 0.85rem;
    }

    .message {
        font-size: 0.85rem;
    }
}

    </style>
</head>
<body>
    <div class="otp-container">
        <h2>Verify OTP</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="otp" required placeholder="" maxlength="6"
                    inputmode="numeric" pattern="\d*" autocomplete="one-time-code" />
                <label>Enter OTP</label>
            </div>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
