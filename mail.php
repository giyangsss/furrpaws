<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require 'vendor/autoload.php';  // Or manually include PHPMailer files

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();  // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Set the SMTP server (can use Gmail, SendGrid, or Mailgun)
    $mail->SMTPAuth = true;  // Enable SMTP authentication
    $mail->Username = 'your-email@gmail.com';  // Gmail username
    $mail->Password = 'your-email-password';  // Gmail password or app-specific password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
    $mail->Port = 587;  // TCP port for TLS

    //Recipients
    $mail->setFrom('your-email@gmail.com', 'Furrpaws');
    $mail->addAddress($to, 'Recipient Name');  // Add a recipient

    // Content
    $mail->isHTML(false);  // Set email format to plain text
    $mail->Subject = 'Appointment Verified';
    $mail->Body    = "Dear " . $email_row['fullname'] . ",\n\nYour appointment has been verified.\n\nThank you!";

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
