<?php
require_once 'config.php';

if (!isset($_SESSION['chat_username'])) {
    $_SESSION['chat_username'] = 'User' . rand(1000, 9999);
}

$username = $_SESSION['chat_username'];
$conn = getConnection();

// Delete old files before deleting messages
$result = $conn->query("SELECT file_path FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY) AND file_path IS NOT NULL");
while ($row = $result->fetch_assoc()) {
    if (file_exists($row['file_path'])) {
        unlink($row['file_path']);
    }
}

$conn->query("DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
$conn->query("DELETE FROM chat_users WHERE last_seen < DATE_SUB(NOW(), INTERVAL 1 DAY)");

$stmt = $conn->prepare("INSERT INTO chat_users (username, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
$stmt->bind_param("s", $username);
$stmt->execute();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Chat - <?php echo htmlspecialchars($username); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="chat.css">
</head>
<body>
    <div class="header">
        <h1>Chat Room - <span id="current-user"><?php echo htmlspecialchars($username); ?></span></h1>
        <form class="username-form" onsubmit="changeUsername(event)">
            <input type="text" id="new_username" placeholder="Ubah nama..." maxlength="20">
            <button type="submit">Ubah</button>
        </form>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                Online (<span id="user-count">0</span>)
            </div>
            <div class="user-list" id="online-users">
                <div class="loading">Memuat...</div>
            </div>
        </div>
        
        <div class="chat-area">
            <div class="messages" id="chat-messages">
                <div class="loading">Memuat pesan...</div>
            </div>
            
            <div class="input-area">
                <div class="file-preview" id="file-preview"></div>
                
                <form class="input-form" onsubmit="sendMessage(event)" id="chat-form">
                    <textarea id="message-input" placeholder="Ketik pesan (Shift+Enter: baris baru)..." rows="1"></textarea>
                    
                    <label class="file-input-label">
                        File
                        <input type="file" id="file-input" onchange="handleFileSelect(event)">
                    </label>
                    
                    <button type="submit">Kirim</button>
                </form>
                
                <div class="input-info">
                    Enter: kirim | Shift+Enter: baris baru | Ctrl+V: tempel gambar | Maks 33 MB
                </div>
            </div>
        </div>
    </div>

    <script src="chat.js"></script>
</body>
</html>