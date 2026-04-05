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

// Honeypot check
if (!empty($_POST['website'])) {
    header('Location: contact.html?status=success');
    exit;
}

$to      = 'info@lagreebythelakestudio.com';
$subject = "New message from $name — Lagree by the Lake";
$body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

// Use GoDaddy localhost relay — no auth needed
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Lagree Website <donotreply@lagreebythelake.com>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";

ini_set('SMTP', 'localhost');
ini_set('smtp_port', '25');

$sent = mail($to, $subject, $body, $headers);

$log = date('Y-m-d H:i:s') . " | sent=$sent | to=$to | name=$name | email=$email\n";
file_put_contents(__DIR__ . '/mail-debug.log', $log, FILE_APPEND);

if ($sent) {
    header('Location: contact.html?status=success');
} else {
    header('Location: contact.html?status=error');
}
exit;
?>
