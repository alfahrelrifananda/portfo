<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['chat_username'])) {
    $_SESSION['chat_username'] = 'User' . rand(1000, 9999);
}

$conn = getConnection();
$username = $_SESSION['chat_username'];

$stmt = $conn->prepare("INSERT INTO chat_users (username, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
$stmt->bind_param("s", $username);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (username, message) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $message);
        $stmt->execute();
    }
    header('Location: chat.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username'])) {
    $new_username = trim($_POST['new_username']);
    if (!empty($new_username) && strlen($new_username) <= 50) {
        $_SESSION['chat_username'] = $new_username;
        header('Location: chat.php');
        exit;
    }
}

$messages = $conn->query("SELECT * FROM chat_messages ORDER BY created_at DESC LIMIT 50");
$messages_array = [];
while ($row = $messages->fetch_assoc()) {
    $messages_array[] = $row;
}
$messages_array = array_reverse($messages_array);

$online_users = $conn->query("SELECT username FROM chat_users WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY username");

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>IRC Chat - <?php echo htmlspecialchars($username); ?></title>
    <meta http-equiv="refresh" content="5">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h2>IRC Chat Room</h2>
    
    <div>
        <strong>You are:</strong> <?php echo htmlspecialchars($username); ?>
        <form method="post" style="display:inline;">
            <input type="text" name="new_username" placeholder="Change username" size="15">
            <input type="submit" value="Change">
        </form>
    </div>
    
    <hr>
    
    <div>
        <strong>Online Users (<?php echo $online_users->num_rows; ?>):</strong>
        <?php while ($user = $online_users->fetch_assoc()): ?>
            <span><?php echo htmlspecialchars($user['username']); ?></span> |
        <?php endwhile; ?>
    </div>
    
    <hr>
    
    <div style="height: 400px; overflow-y: scroll; border: 1px solid black; padding: 10px; background: #f0f0f0;">
        <?php foreach ($messages_array as $msg): ?>
            <div>
                <strong>[<?php echo date('H:i:s', strtotime($msg['created_at'])); ?>]</strong>
                <strong>&lt;<?php echo htmlspecialchars($msg['username']); ?>&gt;</strong>
                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <hr>
    
    <form method="post">
        <input type="text" name="message" placeholder="Type your message..." size="60" autofocus required>
        <input type="submit" value="Send">
    </form>
    
    <p><small>Page auto-refreshes every 5 seconds | <a href="chat_ajax.php">Use AJAX version (faster)</a></small></p>
</body>
</html>