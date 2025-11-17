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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            height: 100vh; 
            display: flex; 
            flex-direction: column;
            background: #f5f5f5;
        }
        
        /* Header */
        .header {
            background: #2c3e50;
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: normal;
        }
        .username-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .username-form input {
            padding: 6px 10px;
            border: none;
            border-radius: 3px;
            font-size: 14px;
        }
        .username-form button {
            padding: 6px 12px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .username-form button:hover {
            background: #229954;
        }
        
        /* Main container */
        .container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 12px 15px;
            background: #ecf0f1;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 14px;
        }
        .user-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 15px;
        }
        .user-item {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        /* Chat area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #fafafa;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 3px solid #3498db;
        }
        .message-header {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .message-time {
            color: #7f8c8d;
            font-size: 11px;
        }
        .message-content {
            margin-top: 5px;
            line-height: 1.4;
            font-size: 14px;
            word-wrap: break-word;
        }
        .message-image {
            margin-top: 8px;
        }
        .message-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 5px;
            cursor: pointer;
        }
        .message-file {
            margin-top: 8px;
            padding: 8px 12px;
            background: #ecf0f1;
            border-radius: 3px;
            display: inline-block;
        }
        .message-file a {
            color: #2c3e50;
            text-decoration: none;
        }
        .message-file a:hover {
            text-decoration: underline;
        }
        
        /* Input area */
        .input-area {
            border-top: 1px solid #ddd;
            padding: 15px;
            background: white;
        }
        .file-preview {
            margin-bottom: 10px;
            padding: 8px;
            background: #e8f5e9;
            border-radius: 3px;
            font-size: 13px;
            display: none;
        }
        .file-preview.active {
            display: block;
        }
        .file-preview button {
            margin-left: 10px;
            padding: 4px 8px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .input-form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .input-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        .input-form input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }
        .file-input-label {
            padding: 10px 15px;
            background: #95a5a6;
            color: white;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
        }
        .file-input-label:hover {
            background: #7f8c8d;
        }
        .file-input-label input {
            display: none;
        }
        .input-form button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
        }
        .input-form button:hover {
            background: #2980b9;
        }
        .input-info {
            margin-top: 8px;
            font-size: 12px;
            color: #7f8c8d;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                max-height: 150px;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }
            .header {
                padding: 10px;
            }
            .header h1 {
                font-size: 16px;
            }
            .username-form {
                width: 100%;
            }
            .username-form input {
                flex: 1;
            }
            .input-form {
                flex-wrap: wrap;
            }
            .input-form input[type="text"] {
                width: 100%;
            }
            .file-input-label,
            .input-form button {
                flex: 1;
            }
        }
        
        /* Loading state */
        .loading {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
    </style>
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
                    <input type="text" id="message-input" placeholder="Ketik pesan atau tempel gambar (Ctrl+V)..." autofocus>
                    
                    <label class="file-input-label">
                        File
                        <input type="file" id="file-input" onchange="handleFileSelect(event)">
                    </label>
                    
                    <button type="submit">Kirim</button>
                </form>
                
                <div class="input-info">
                    Pesan dihapus otomatis setelah 1 hari. Maks 33 MB. Tempel gambar: Ctrl+V
                </div>
            </div>
        </div>
    </div>

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
                    showFilePreview(file);
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
                alert('File terlalu besar! Ukuran maksimal 33 MB.');
                e.target.value = '';
                return;
            }

            selectedFile = file;
            showFilePreview(file);
        }

        function showFilePreview(file) {
            const preview = document.getElementById('file-preview');
            preview.className = 'file-preview active';
            preview.innerHTML = '<strong>Terlampir:</strong> ' + escapeHtml(file.name) + ' (' + formatFileSize(file.size) + ')' +
                '<button type="button" onclick="clearFile()">Hapus</button>';
        }

        function clearFile() {
            selectedFile = null;
            document.getElementById('file-input').value = '';
            document.getElementById('file-preview').className = 'file-preview';
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
                
                if (lastMessageId === 0 && data.messages && data.messages.length === 0) {
                    chatDiv.innerHTML = '<div class="loading">Belum ada pesan. Kirim pesan pertama!</div>';
                }
                
                if (data.messages && data.messages.length > 0) {
                    if (lastMessageId === 0) {
                        chatDiv.innerHTML = '';
                    }
                    
                    data.messages.forEach(function(msg) {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'message';
                        
                        const time = new Date(msg.created_at).toLocaleTimeString();
                        let html = '<div class="message-header">' + escapeHtml(msg.username) + 
                                  ' <span class="message-time">' + time + '</span></div>';
                        
                        if (msg.message) {
                            html += '<div class="message-content">' + 
                                   escapeHtml(msg.message).replace(/\n/g, '<br>') + '</div>';
                        }
                        
                        if (msg.file_path) {
                            if (msg.file_type === 'image') {
                                html += '<div class="message-image">' +
                                    '<a href="' + escapeHtml(msg.file_path) + '" target="_blank">' +
                                    '<img src="' + escapeHtml(msg.file_path) + '" alt="' + escapeHtml(msg.file_name) + '"></a>' +
                                    '</div>';
                            } else {
                                html += '<div class="message-file">' +
                                    '<a href="' + escapeHtml(msg.file_path) + '" download="' + escapeHtml(msg.file_name) + '">' +
                                    'Unduh: ' + escapeHtml(msg.file_name) + '</a>' +
                                    '</div>';
                            }
                        }
                        
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
                if (data.users && data.users.length > 0) {
                    usersDiv.innerHTML = data.users.map(function(u) { 
                        return '<div class="user-item">' + escapeHtml(u.username) + '</div>'; 
                    }).join('');
                    document.getElementById('user-count').textContent = data.users.length;
                } else {
                    usersDiv.innerHTML = '<div class="loading">Tidak ada user online</div>';
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