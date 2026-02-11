<?php
// Simple mail handler for the contact form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Business Enquiry');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email';
    exit;
}

// Sanitize for safe email body
$clean_name = filter_var($name, FILTER_SANITIZE_STRING);
$clean_subject = filter_var($subject, FILTER_SANITIZE_STRING);
$clean_message = filter_var($message, FILTER_SANITIZE_STRING);

$to = 'business.development@seraphichk.com';
$from = 'enquiry@seraphichk.com';

$body = "You have a new message from your website contact form:\n\n";
$body .= "Name: " . $clean_name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Subject: " . $clean_subject . "\n\n";
$body .= "Message:\n" . $clean_message . "\n\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

$headers = "From: Seraphic Website <" . $from . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Attempt to send
if (mail($to, $clean_subject, $body, $headers)) {
    http_response_code(200);
    echo 'OK';
} else {
    http_response_code(500);
    echo 'Error sending message';
}

// Note: If your host restricts PHP mail(), consider using SMTP via PHPMailer
// or a transactional email service (SendGrid, Mailgun) for reliability.

?>
