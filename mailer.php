<?php

// Load the three PHPMailer class files we downloaded
require_once 'phpmailer/Exception.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';

// We need to use these PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load our config settings
require_once 'config.php';

function send_email($to, $subject, $body) {

    // Create a new PHPMailer object
    // 'true' means it will throw exceptions if something goes wrong
    $mail = new PHPMailer(true);

    try {
        // --- SMTP Settings ---
        // Tell PHPMailer to use SMTP (Simple Mail Transfer Protocol)
        $mail->isSMTP();

        // The address of the mail server (smtp.gmail.com for Gmail)
        $mail->Host = MAIL_HOST;

        // Turn on SMTP authentication (we need to log in to send email)
        $mail->SMTPAuth = true;

        // Your Gmail address
        $mail->Username = MAIL_USERNAME;

        // Your Gmail App Password (not your real password!)
        $mail->Password = MAIL_PASSWORD;

        // Use TLS encryption on port 587 (standard secure setting)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_PORT;

        // --- Email Content ---
        // Set who the email is FROM
        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);

        // Set who the email is going TO
        $mail->addAddress($to);

        // Tell PHPMailer the email body is HTML
        $mail->isHTML(true);

        // Set the subject line
        $mail->Subject = $subject;

        // Set the HTML body of the email
        $mail->Body = $body;

        // Actually send the email!
        $mail->send();

        // If we reach here, email was sent successfully
        return true;

    } catch (Exception $e) {
        // If something went wrong, show a basic error message
        // $mail->ErrorInfo contains the detailed PHPMailer error
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}