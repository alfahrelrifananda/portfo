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
</head>
<body>
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <td bgcolor="#f0f0f0" width="250" valign="top">
                <h3>User Online (<span id="user-count">0</span>)</h3>
                <div id="online-users">Memuat...</div>
            </td>
            <td valign="top">
                <table width="100%">
                    <tr>
                        <td bgcolor="#e8f4f8">
                            <strong>Anda:</strong> <span id="current-user"><?php echo htmlspecialchars($username); ?></span>
                            <form onsubmit="changeUsername(event)">
                                <input type="text" id="new_username" placeholder="Nama User baru" size="15">
                                <button type="submit">Ubah Nama</button>
                            </form>
                        </td>
                    </tr>
                </table>
                
                <hr>
                
                <div id="chat-messages" style="height: 400px; overflow-y: scroll; border: 2px solid #ccc; padding: 10px; background: #fafafa;">
                    Memuat pesan...
                </div>
                
                <hr>
                
                <form onsubmit="sendMessage(event)" id="chat-form">
                    <table width="100%">
                        <tr>
                            <td>
                                <input type="text" id="message-input" placeholder="Ketik pesan atau tempel gambar (Ctrl+V)..." size="80" autofocus>
                            </td>
                            <td>
                                <input type="file" id="file-input" onchange="handleFileSelect(event)">
                            </td>
                            <td>
                                <button type="submit">Kirim</button>
                            </td>
                        </tr>
                    </table>
                    <div id="file-preview"></div>
                </form>
                
                <p><small>Pesan dan file otomatis terhapus setelah 1 hari. Ukuran file maksimal: 33 MB. Tempel gambar dengan Ctrl+V!</small></p>
            </td>
        </tr>
    </table>

    <script>
        let lastMessageId = 0;
        let updateInterval;
        let selectedFile = null;

        document.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;
            
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                
                if (item.type.indexOf('image') !== -1) {
                    e.preventDefault();
                    const blob = item.getAsFile();
                    const filename = 'clipboard-' + Date.now() + '.' + item.type.split('/')[1];
                    const file = new File([blob], filename, { type: item.type });
                    
                    selectedFile = file;
                    const preview = document.getElementById('file-preview');
                    preview.innerHTML = '<strong>Terlampir:</strong> ' + escapeHtml(file.name) + ' (' + formatFileSize(file.size) + ') ' +
                        '<button type="button" onclick="clearFile()">Hapus</button>';
                    
                    document.getElementById('message-input').focus();
                    return;
                }
            }
        });

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;

            const maxSize = 33 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File terlalu besar! Ukuran maksimal adalah 33 MB.');
                e.target.value = '';
                return;
            }

            selectedFile = file;
            const preview = document.getElementById('file-preview');
            preview.innerHTML = '<strong>Terlampir:</strong> ' + escapeHtml(file.name) + ' (' + formatFileSize(file.size) + ') ' +
                '<button type="button" onclick="clearFile()">Hapus</button>';
        }

        function clearFile() {
            selectedFile = null;
            document.getElementById('file-input').value = '';
            document.getElementById('file-preview').innerHTML = '';
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function sendMessage(e) {
            e.preventDefault();
            const message = document.getElementById('message-input').value.trim();
            
            if (!message && !selectedFile) return;

            const formData = new FormData();
            formData.append('action', 'send');
            if (message) formData.append('message', message);
            if (selectedFile) formData.append('file', selectedFile);

            fetch('chat_api.php', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('message-input').value = '';
                    clearFile();
                    loadMessages();
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(function(err) {
                console.error('Error:', err);
                alert('Gagal mengirim pesan');
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
            .then(function(response) { return response.json(); })
            .then(function(data) {
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
            .then(function(response) { return response.json(); })
            .then(function(data) {
                const chatDiv = document.getElementById('chat-messages');
                
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(function(msg) {
                        const msgDiv = document.createElement('div');
                        const time = new Date(msg.created_at).toLocaleTimeString();
                        let html = '<p><strong>[' + time + '] ' + escapeHtml(msg.username) + ':</strong><br>';
                        
                        if (msg.message) {
                            html += escapeHtml(msg.message).replace(/\n/g, '<br>');
                        }
                        
                        if (msg.file_path) {
                            html += '<br>';
                            if (msg.file_type === 'image') {
                                html += '<a href="' + escapeHtml(msg.file_path) + '" target="_blank">' +
                                    '<img src="' + escapeHtml(msg.file_path) + '" alt="' + escapeHtml(msg.file_name) + '" width="300"></a>';
                            } else {
                                html += '<a href="' + escapeHtml(msg.file_path) + '" download="' + escapeHtml(msg.file_name) + '">' +
                                    'Unduh: ' + escapeHtml(msg.file_name) + '</a>';
                            }
                        }
                        
                        html += '</p><hr>';
                        msgDiv.innerHTML = html;
                        chatDiv.appendChild(msgDiv);
                        lastMessageId = msg.id;
                    });
                    chatDiv.scrollTop = chatDiv.scrollHeight;
                }
            });
        }

        function loadUsers() {
            fetch('chat_api.php?action=users')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                const usersDiv = document.getElementById('online-users');
                if (data.users) {
                    usersDiv.innerHTML = data.users.map(function(u) { 
                        return escapeHtml(u.username); 
                    }).join('<br>');
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

        updateInterval = setInterval(function() {
            loadMessages();
            loadUsers();
        }, 2000);
    </script>
</body>
</html>