<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Adjust path if needed

$mail = new PHPMailer(true);

try {
    // Server config
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ballescaian123@gmail.com';         // 👈 your Gmail
    $mail->Password = 'fmln rqqn jldd tjjv';       // 👈 your 16-char App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // From & To
    $mail->setFrom('ballescaian123@gmail.com', 'Vital Care Clinic');
    $mail->addAddress('ballescaian24@gmail.com', 'Test Student');

    // Optional: reply-to
    $mail->addReplyTo('noreply@philcst.edu.ph', 'Do Not Reply');

    // Email Content
    $mail->Subject = '📢 Test Notification';
    $mail->isHTML(true);
    $mail->Body    = "<h3>This is a test email from MERS system.</h3><p>Please ignore.</p>";
    $mail->AltBody = "This is a test email from MERS system. Please ignore.";

    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
}
