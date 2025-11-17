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