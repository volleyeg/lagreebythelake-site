<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$name    = strip_tags(trim($_POST['name'] ?? ''));
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$message = strip_tags(trim($_POST['message'] ?? ''));

if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.html?status=error');
    exit;
}

// Honeypot check - bots fill this in, humans don't
if (!empty($_POST['website'])) {
    header('Location: contact.html?status=success'); // fake success to confuse bots
    exit;
}

// Use sendmail path directly - more reliable on GoDaddy cPanel
$to      = 'info@lagreebythelakestudio.com';
$subject = "Contact Form: Message from $name";
$body    = "You have a new message from the Lagree by the Lake website contact form.\n\n";
$body   .= "Name: $name\n";
$body   .= "Email: $email\n\n";
$body   .= "Message:\n$message\n\n";
$body   .= "---\nSent from lagreebythelake.com/contact.html";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Lagree by the Lake <info@lagreebythelakestudio.com>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail($to, $subject, $body, $headers, '-f info@lagreebythelake.com');

if ($sent) {
    header('Location: contact.html?status=success');
} else {
    // Log the error for diagnosis
    $error = error_get_last();
    $log = date('Y-m-d H:i:s') . " | mail() failed | to: $to | error: " . ($error ? $error['message'] : 'unknown') . "\n";
    file_put_contents(__DIR__ . '/mail-debug.log', $log, FILE_APPEND);
    header('Location: contact.html?status=error');
}
exit;
?>
