<?php
require_once 'config.php';

if (!isset($_SESSION['chat_username'])) {
    $_SESSION['chat_username'] = 'User' . rand(1000, 9999);
}

$username = $_SESSION['chat_username'];

$conn = getConnection();

// Clean up old data (older than 1 day)
$conn->query("DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
$conn->query("DELETE FROM chat_users WHERE last_seen < DATE_SUB(NOW(), INTERVAL 1 DAY)");

$stmt = $conn->prepare("INSERT INTO chat_users (username, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
$stmt->bind_param("s", $username);
$stmt->execute();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat - <?php echo htmlspecialchars($username); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>Chat Room</h1>
    
    <p>
        <strong>You:</strong> <span id="current-user"><?php echo htmlspecialchars($username); ?></span>
        <form onsubmit="changeUsername(event)" style="display:inline;">
            <input type="text" id="new_username" placeholder="New username" size="15">
            <button type="submit">Change</button>
        </form>
    </p>

    <div>
        <h3>Online Users (<span id="user-count">0</span>)</h3>
        <div id="online-users">Loading...</div>
    </div>

    <hr>

    <div id="chat-messages" style="max-height: 400px; overflow-y: auto; border: 1px solid; padding: 10px; margin: 10px 0;">
        Loading messages...
    </div>

    <form onsubmit="sendMessage(event)">
        <input type="text" id="message-input" placeholder="Type your message..." style="width: 80%; max-width: 600px;" autofocus required>
        <button type="submit">Send</button>
    </form>

    <hr>
    <p><small>Messages older than 1 day are automatically deleted</small></p>

    <script>
        let lastMessageId = 0;
        let updateInterval;

        function sendMessage(e) {
            e.preventDefault();
            const message = document.getElementById('message-input').value.trim();
            if (!message) return;

            fetch('chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=send&message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(() => {
                document.getElementById('message-input').value = '';
                loadMessages();
            });
        }

        function changeUsername(e) {
            e.preventDefault();
            const newUsername = document.getElementById('new_username').value.trim();
            if (!newUsername) return;

            fetch('chat_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=change_username&username=' + encodeURIComponent(newUsername)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('current-user').textContent = newUsername;
                    document.getElementById('new_username').value = '';
                    loadMessages();
                    loadUsers();
                }
            });
        }

        function loadMessages() {
            fetch('chat_api.php?action=messages&last_id=' + lastMessageId)
            .then(response => response.json())
            .then(data => {
                const chatDiv = document.getElementById('chat-messages');
                
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const msgDiv = document.createElement('div');
                        const time = new Date(msg.created_at).toLocaleTimeString();
                        msgDiv.innerHTML = '<strong>[' + time + ']</strong> <strong>' + 
                            escapeHtml(msg.username) + ':</strong> ' + 
                            escapeHtml(msg.message).replace(/\n/g, '<br>');
                        chatDiv.appendChild(msgDiv);
                        lastMessageId = msg.id;
                    });
                    chatDiv.scrollTop = chatDiv.scrollHeight;
                }
            });
        }

        function loadUsers() {
            fetch('chat_api.php?action=users')
            .then(response => response.json())
            .then(data => {
                const usersDiv = document.getElementById('online-users');
                if (data.users) {
                    usersDiv.innerHTML = data.users.map(u => escapeHtml(u.username)).join('<br>');
                    document.getElementById('user-count').textContent = data.users.length;
                }
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        loadMessages();
        loadUsers();

        updateInterval = setInterval(() => {
            loadMessages();
            loadUsers();
        }, 2000);
    </script>
</body>
</html>