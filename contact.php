<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $msg = htmlspecialchars($_POST['message']);

    $to = "pahrel1234@gmail.com";
    $full_subject = "Personal Site Contact: " . $subject;
    $body = "You have a new message from your personal site:\n\n" .
            "Name: $name\n" .
            "Email: $email\n\n" .
            "Message:\n$msg\n";
    $headers = "From: $email\r\n" .
               "Reply-To: $email\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $full_subject, $body, $headers)) {
        $message = "Thank you for your message!";
    } else {
        $message = "Sorry, something went wrong — the email could not be sent.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - AlfahrelRifananda</title>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="projects.php">Projects</a>
        <a href="blog.php">Blog</a>
        <a href="contact.php">Contact</a>
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
            <a href="dashboard.php">Dashboard</a>
        <?php endif; ?>
    </nav>

    <hr>

    <h1>Contact Me</h1>

    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
        <hr>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label for="name">Name *</label><br>
            <input type="text" id="name" name="name" required size="40">
        </p>

        <p>
            <label for="email">Email *</label><br>
            <input type="email" id="email" name="email" required size="40">
        </p>

        <p>
            <label for="subject">Subject *</label><br>
            <input type="text" id="subject" name="subject" required size="40">
        </p>

        <p>
            <label for="message">Message *</label><br>
            <textarea id="message" name="message" rows="8" cols="40" required></textarea>
        </p>

        <p>
            <button type="submit">Send Message</button>
        </p>
    </form>
</body>
</html>
