let lastMessageId = 0;
let updateInterval;
let selectedFile = null;
let isLoading = false;
let currentUser = '';

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

// Handle Shift+Enter for new line, Enter to send
document.getElementById('message-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chat-form').dispatchEvent(new Event('submit'));
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

function deleteMessage(messageId) {
    if (!confirm('Hapus pesan ini?')) return;
    
    fetch('chat_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete&message_id=' + messageId
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            // Remove the message from DOM
            const msgElement = document.querySelector('[data-message-id="' + messageId + '"]');
            if (msgElement) {
                msgElement.remove();
            }
        } else {
            alert('Gagal menghapus pesan: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(function(err) {
        console.error('Error deleting message:', err);
        alert('Gagal menghapus pesan');
    });
}

function sendMessage(e) {
    e.preventDefault();
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if (!message && !selectedFile) return;

    // Disable send button to prevent double sending
    const sendButton = document.querySelector('.input-form button[type="submit"]');
    sendButton.disabled = true;

    const formData = new FormData();
    formData.append('action', 'send');
    if (message) formData.append('message', message);
    if (selectedFile) formData.append('file', selectedFile);

    fetch('chat_api.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        // Check if response is ok before parsing
        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }
        return response.text(); // Get as text first to debug
    })
    .then(function(text) {
        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            if (data.success) {
                messageInput.value = '';
                clearFile();
                // Wait a bit before loading to ensure message is in DB
                setTimeout(function() {
                    loadMessages();
                }, 100);
            } else if (data.error) {
                alert('Error: ' + data.error);
            } else {
                alert('Gagal mengirim pesan');
            }
        } catch (parseError) {
            // If JSON parsing fails, show what we got
            console.error('Response was not JSON:', text);
            alert('Server error: Response was not valid JSON. Check console for details.');
        }
        sendButton.disabled = false;
    })
    .catch(function(err) {
        console.error('Error:', err);
        alert('Gagal mengirim pesan: ' + err.message);
        sendButton.disabled = false;
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
            currentUser = newUsername;
            lastMessageId = 0; // Reset to reload all messages
            loadMessages();
            loadUsers();
        }
    });
}

function loadMessages() {
    if (isLoading) return; // Prevent concurrent loads
    isLoading = true;

    fetch('chat_api.php?action=messages&last_id=' + lastMessageId)
    .then(function(response) { return response.json(); })
    .then(function(data) {
        const chatDiv = document.getElementById('chat-messages');
        
        // Update current user
        if (data.current_user) {
            currentUser = data.current_user;
        }
        
        if (lastMessageId === 0 && data.messages && data.messages.length === 0) {
            chatDiv.innerHTML = '<div class="loading">Belum ada pesan. Kirim pesan pertama!</div>';
        }
        
        if (data.messages && data.messages.length > 0) {
            if (lastMessageId === 0) {
                chatDiv.innerHTML = '';
            }
            
            const shouldScroll = chatDiv.scrollHeight - chatDiv.scrollTop <= chatDiv.clientHeight + 100;
            
            data.messages.forEach(function(msg) {
                // Debug logging
                console.log('Message:', msg.id, 'Username:', msg.username, 'Current User:', currentUser, 'can_delete:', msg.can_delete);
                
                const msgDiv = document.createElement('div');
                msgDiv.className = 'message';
                msgDiv.setAttribute('data-message-id', msg.id);
                
                const time = new Date(msg.created_at).toLocaleTimeString();
                let html = '<div class="message-header">';
                html += '<span class="message-user">' + escapeHtml(msg.username) + 
                       ' <span class="message-time">' + time + '</span></span>';
                
                // Add delete button if user owns the message
                if (msg.can_delete === true || msg.can_delete === 1 || msg.can_delete === '1') {
                    html += '<button class="delete-btn" onclick="deleteMessage(' + msg.id + ')" title="Hapus pesan">✕</button>';
                }
                
                html += '</div>';
                
                if (msg.message) {
                    // Format message with line breaks and preserve formatting
                    const formattedMessage = escapeHtml(msg.message)
                        .replace(/\n/g, '<br>')
                        .replace(/  /g, '&nbsp;&nbsp;');
                    html += '<div class="message-content">' + formattedMessage + '</div>';
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
                            '📎 ' + escapeHtml(msg.file_name) + '</a>' +
                            '</div>';
                    }
                }
                
                msgDiv.innerHTML = html;
                chatDiv.appendChild(msgDiv);
                lastMessageId = msg.id;
            });
            
            // Only auto-scroll if user was near bottom
            if (shouldScroll) {
                chatDiv.scrollTop = chatDiv.scrollHeight;
            }
        }
        isLoading = false;
    })
    .catch(function(err) {
        console.error('Error loading messages:', err);
        isLoading = false;
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
    })
    .catch(function(err) {
        console.error('Error loading users:', err);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initial load
loadMessages();
loadUsers();

// Poll for updates every 2 seconds
updateInterval = setInterval(function() {
    loadMessages();
    loadUsers();
}, 2000);