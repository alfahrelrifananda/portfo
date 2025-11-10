<?php
require_once 'config.php';

if (!isset($_SESSION['chat_username'])) {
    $_SESSION['chat_username'] = 'User' . rand(1000, 9999);
}

$username = $_SESSION['chat_username'];

$conn = getConnection();
$stmt = $conn->prepare("INSERT INTO chat_users (username, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
$stmt->bind_param("s", $username);
$stmt->execute();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>IRC Chat - <?php echo htmlspecialchars($username); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <table width="100%" border="1" cellpadding="10">
        <tr>
            <td colspan="2" bgcolor="#cccccc">
                <h2>IRC Chat Room</h2>
            </td>
        </tr>
        <tr>
            <td width="70%" valign="top">
                <div>
                    <strong>You are:</strong> <span id="current-user"><?php echo htmlspecialchars($username); ?></span>
                    <form onsubmit="changeUsername(event)" style="display:inline;">
                        <input type="text" id="new_username" placeholder="New username" size="15">
                        <input type="submit" value="Change">
                    </form>
                </div>
                <hr>
                <div id="chat-messages" style="height: 400px; overflow-y: scroll; border: 1px solid black; padding: 10px; background: #f0f0f0;">
                    Loading messages...
                </div>
                <hr>
                <form onsubmit="sendMessage(event)">
                    <table width="100%">
                        <tr>
                            <td width="80%">
                                <input type="text" id="message-input" placeholder="Type your message..." style="width: 100%;" autofocus required>
                            </td>
                            <td width="20%">
                                <input type="submit" value="Send" style="width: 100%;">
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
            <td width="30%" valign="top" bgcolor="#e0e0e0">
                <strong>Online Users</strong>
                <div id="online-users">
                    Loading...
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" bgcolor="#cccccc">
                <small>Auto-updates every 2 seconds</small>
            </td>
        </tr>
    </table>

    <script>
        let lastMessageId = 0;
        let updateInterval;

        function sendMessage(e) {
            e.preventDefault();
            const message = document.getElementById('message-input').value.trim();
            if (!message) return;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'chat_api.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('message-input').value = '';
                    loadMessages();
                }
            };
            xhr.send('action=send&message=' + encodeURIComponent(message));
        }

        function changeUsername(e) {
            e.preventDefault();
            const newUsername = document.getElementById('new_username').value.trim();
            if (!newUsername) return;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'chat_api.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('current-user').textContent = newUsername;
                    document.getElementById('new_username').value = '';
                    loadMessages();
                    loadUsers();
                }
            };
            xhr.send('action=change_username&username=' + encodeURIComponent(newUsername));
        }

        function loadMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'chat_api.php?action=messages&last_id=' + lastMessageId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    const chatDiv = document.getElementById('chat-messages');
                    
                    if (response.messages && response.messages.length > 0) {
                        response.messages.forEach(function(msg) {
                            const msgDiv = document.createElement('div');
                            const time = new Date(msg.created_at).toLocaleTimeString();
                            msgDiv.innerHTML = '<strong>[' + time + ']</strong> <strong>&lt;' + 
                                escapeHtml(msg.username) + '&gt;</strong> ' + 
                                escapeHtml(msg.message).replace(/\n/g, '<br>');
                            chatDiv.appendChild(msgDiv);
                            lastMessageId = msg.id;
                        });
                        chatDiv.scrollTop = chatDiv.scrollHeight;
                    }
                }
            };
            xhr.send();
        }

        function loadUsers() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'chat_api.php?action=users', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    const usersDiv = document.getElementById('online-users');
                    if (response.users) {
                        usersDiv.innerHTML = '<hr>';
                        response.users.forEach(function(user) {
                            usersDiv.innerHTML += escapeHtml(user.username) + '<br>';
                        });
                        usersDiv.innerHTML += '<hr><small>Total: ' + response.users.length + '</small>';
                    }
                }
            };
            xhr.send();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        loadMessages();
        loadUsers();

        updateInterval = setInterval(function() {
            loadMessages();
            loadUsers();
        }, 2000);
    </script>
</body>
</html>