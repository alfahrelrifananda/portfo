<?php
ini_set('sendmail_path', '/data/data/com.termux/files/usr/bin/sendmail -t -i');
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
$title = 'Home - AlfahrelRifananda';
include 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $msg = htmlspecialchars($_POST['message']);

    $to = "pahrel1234@gmail.com";
    $full_subject = "Personal Contact: " . $subject;
    $body = "You have a new message from your site:\n\n" .
            "Name: $name\n" .
            "Email: $email\n\n" .
            "Message:\n$msg\n";
    $headers = "From: $email\r\n" .
               "Reply-To: $email\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    $result = @mail($to, $full_subject, $body, $headers);

    // $log_file = __DIR__ . '/mail_debug.log';
    // $timestamp = date('Y-m-d H:i:s');
    // $cmd = ini_get('sendmail_path');
    // $log_message = "[$timestamp] mail() called\n";
    // $log_message .= "sendmail_path: $cmd\n";
    // $log_message .= "To: $to\nFrom: $email\nSubject: $full_subject\n";
    // $log_message .= "mail() returned: " . ($result ? 'true' : 'false') . "\n\n";
    // file_put_contents($log_file, $log_message, FILE_APPEND);

    if ($result) {
        $message = "Email berhasil terkirim!";
    } else {
        $message = "Tidak bisa mengirim email :(.";
    }
}
?>

<h1>Kontak</h1>

    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
        <hr>
    <?php endif; ?>

    <form method="POST" action="">
        <p><label>Nama *</label><br><input type="text" name="name" required></p>
        <p><label>Email *</label><br><input type="email" name="email" required></p>
        <p><label>Subyek *</label><br><input type="text" name="subject" required></p>
        <p><label>Pesan *</label><br><textarea name="message" rows="6" required></textarea></p>
        <p><button type="submit">Kirim pesan</button></p>
    </form>

<?php include 'footer.php'; ?>
