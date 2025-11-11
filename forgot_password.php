<?php
session_start();
include 'includes/db_connect.php';

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];
            $otp = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
            $update = $conn->prepare("UPDATE users SET otp_code=?, otp_expires=? WHERE user_id=?");
            $update->bind_param("ssi", $otp, $expires, $user_id);
            $update->execute();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ballescaian123@gmail.com';
                $mail->Password   = 'fmlnrqqnjlddtjjv';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('ballescaian123@gmail.com', 'Medical Records System');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code for Password Reset';
                $mail->Body    = "<p>Hello,</p><p>You requested a password reset. Use the OTP below:</p><h2>$otp</h2><p>This code will expire in 10 minutes.</p>";

                $mail->send();
                $_SESSION['reset_email'] = $email;
                header("Location: verify_otp.php");
                exit;
            } catch (Exception $e) {
                $message = "Error sending OTP: {$mail->ErrorInfo}";
            }
        } else {
            $message = "No account found with that email.";
        }
    } else {
        $message = "Please enter your email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password</title>
    <style>
        /* =========================
        FORGOT PASSWORD PAGE
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
        .forgot-container {
            width: 100%;
            max-width: 400px;
            min-width: 300px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        /* HEADER */
        .forgot-container h2 {
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        /* FORM */
        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
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
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            background: #007BFF;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        /* LINKS */
        p a {
            color: #007BFF;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }

        /* MESSAGES */
        .message {
            color: red;
            font-size: 0.95rem;
            margin-top: 15px;
            text-align: center;
        }

        /* =========================
        MOBILE RESPONSIVE
        ========================= */
        @media (max-width: 480px) {
            .forgot-container {
                width: 90%;
                padding: 20px;
            }

            h2 {
                font-size: 1.5rem;
                margin-bottom: 1.2rem;
            }

            input, button {
                font-size: 1.2rem;
                padding: 10px;
                width: 100%;
                max-width: 100%;
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
            .forgot-container {
                padding: 15px;
            }

            h2 {
                font-size: 1.4rem;
            }

            input, button {
                font-size: 1.1rem;
                padding: 8px;
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
    <div class="forgot-container">
        <h2>Forgot Password</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" required placeholder=" " />
                <label>Email</label>
            </div>
            <button type="submit">Continue</button>
        </form>
        <p><a href="index.php">Back to Login</a></p>
    </div>
</body>
</html>
