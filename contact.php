<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $msg = htmlspecialchars($_POST['message']);

    $to = "your@gmail.com"; // change this to your actual address
    $full_subject = "Portfolio Contact: " . $subject;
    $body = "You have a new message from your portfolio site:\n\n" .
            "Name: $name\n" .
            "Email: $email\n\n" .
            "Message:\n$msg\n";
    $headers = "From: $email\r\n" .
               "Reply-To: $email\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    // Force mail() debug output
    $result = @mail($to, $full_subject, $body, $headers);

    // Write debug info to file
    $log_file = __DIR__ . '/mail_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $cmd = ini_get('sendmail_path');
    $log_message = "[$timestamp] mail() called\n";
    $log_message .= "sendmail_path: $cmd\n";
    $log_message .= "To: $to\nFrom: $email\nSubject: $full_subject\n";
    $log_message .= "mail() returned: " . ($result ? 'true' : 'false') . "\n\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    if ($result) {
        $message = "✅ Email successfully sent!";
    } else {
        $message = "❌ Could not send email. Check <code>mail_debug.log</code> and <code>~/.msmtp.log</code>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - My Portfolio</title>
</head>
<body>
    <nav>
        <a href="index.php">[Home]</a>
        <a href="about.php">[About]</a>
        <a href="projects.php">[Projects]</a>
        <a href="blog.php">[Blog]</a>
        <a href="contact.php">[Contact]</a>
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
            <a href="dashboard.php">[Dashboard]</a>
        <?php endif; ?>
    </nav>

    <hr>

    <h1>Contact Me</h1>

    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
        <hr>
        <p>🧩 Debug info (server):</p>
        <pre>
<?php
if (file_exists(__DIR__ . '/mail_debug.log')) {
    echo htmlspecialchars(file_get_contents(__DIR__ . '/mail_debug.log'));
} else {
    echo "No log file found.";
}
?>
        </pre>
    <?php endif; ?>

    <form method="POST" action="">
        <p><label>Name *</label><br><input type="text" name="name" required></p>
        <p><label>Email *</label><br><input type="email" name="email" required></p>
        <p><label>Subject *</label><br><input type="text" name="subject" required></p>
        <p><label>Message *</label><br><textarea name="message" rows="6" required></textarea></p>
        <p><button type="submit">Send Message</button></p>
    </form>
</body>
</html>
