<?php
// Simple mail handler for the contact form with Google reCAPTCHA v3
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Your reCAPTCHA Secret Key
$recaptcha_secret = '6LflyG0sAAAAAA_GAOzDASK3N89iK7FzyYzP5Qh-';
$recaptcha_token = trim($_POST['recaptcha_token'] ?? '');

// Verify reCAPTCHA token
if (empty($recaptcha_token)) {
    http_response_code(400);
    echo 'reCAPTCHA token missing';
    exit;
}

// Verify token with Google
$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_post = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_token
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
        'content' => http_build_query($recaptcha_post),
        'timeout' => 5
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($recaptcha_url, false, $context);

if ($response === false) {
    http_response_code(500);
    echo 'reCAPTCHA verification failed';
    exit;
}

$response_data = json_decode($response, true);

// Check if verification was successful and score is acceptable
// reCAPTCHA v3 returns a score between 0.0 and 1.0
// Score closer to 1.0 means more likely human, closer to 0.0 means likely bot
// Threshold of 0.5 is recommended; adjust as needed
if (!$response_data['success'] || ($response_data['score'] ?? 0) < 0.5) {
    http_response_code(400);
    echo 'reCAPTCHA verification failed (possible bot activity)';
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

$to = 'alokdesai.in@gmail.com';
$from = 'enquiry@seraphichk.com';

$body = "You have a new message from your website contact form:\n\n";
$body .= "Name: " . $clean_name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Subject: " . $clean_subject . "\n\n";
$body .= "Message:\n" . $clean_message . "\n\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
$body .= "reCAPTCHA Score: " . ($response_data['score'] ?? 'N/A') . "\n";

$headers = "From: Seraphic Website <" . $from . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Log form submission for debugging
$log_file = __DIR__ . '/contact_form_log.txt';
$log_entry = "[" . date('Y-m-d H:i:s') . "] Form submission:\n";
$log_entry .= "  Name: $clean_name\n";
$log_entry .= "  Email: $email\n";
$log_entry .= "  Subject: $clean_subject\n";
$log_entry .= "  Recipient: $to\n";

// Attempt to send
$mail_sent = mail($to, $clean_subject, $body, $headers);
$log_entry .= "  Mail result: " . ($mail_sent ? 'SUCCESS' : 'FAILED') . "\n";
$log_entry .= "---\n";

// Write to log file
error_log($log_entry, 3, $log_file);

if ($mail_sent) {
    http_response_code(200);
    echo 'OK';
} else {
    http_response_code(500);
    echo 'Error sending message';
}

// Note: If your host restricts PHP mail(), consider using SMTP via PHPMailer
// or a transactional email service (SendGrid, Mailgun) for reliability.
?>
