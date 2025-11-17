<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['chat_username'])) {
    $_SESSION['chat_username'] = 'User' . rand(1000, 9999);
}

$conn = getConnection();
$username = $_SESSION['chat_username'];

$stmt = $conn->prepare("INSERT INTO chat_users (username, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
$stmt->bind_param("s", $username);
$stmt->execute();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'send':
        if (isset($_POST['message']) || isset($_FILES['file'])) {
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            $file_path = null;
            $file_name = null;
            $file_type = null;
            
            // Handle file upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/';
                
                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        echo json_encode(['success' => false, 'error' => 'Failed to create uploads directory.']);
                        break;
                    }
                }
                
                // Check if directory is writable
                if (!is_writable($upload_dir)) {
                    echo json_encode(['success' => false, 'error' => 'Uploads directory is not writable.']);
                    break;
                }
                
                $file_size = $_FILES['file']['size'];
                $max_size = 33 * 1024 * 1024; // 33 MB
                
                if ($file_size > $max_size) {
                    echo json_encode(['success' => false, 'error' => 'File too large. Max 33 MB.']);
                    break;
                }
                
                $file_name = basename($_FILES['file']['name']);
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
                $full_path = $upload_dir . $unique_name;
                $file_path = 'uploads/' . $unique_name; // Relative path for database
                
                // Determine file type
                $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
                if (in_array($file_extension, $image_extensions)) {
                    $file_type = 'image';
                } else {
                    $file_type = 'file';
                }
                
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $full_path)) {
                    $error_msg = 'Failed to upload file. Error: ' . error_get_last()['message'];
                    echo json_encode(['success' => false, 'error' => $error_msg]);
                    break;
                }
            } else if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $error_msg = $upload_errors[$_FILES['file']['error']] ?? 'Unknown upload error';
                echo json_encode(['success' => false, 'error' => $error_msg]);
                break;
            }
            
            if (!empty($message) || $file_path) {
                $stmt = $conn->prepare("INSERT INTO chat_messages (username, message, file_path, file_name, file_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $username, $message, $file_path, $file_name, $file_type);
                $stmt->execute();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        }
        break;

    case 'messages':
        $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
        $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE id > ? ORDER BY created_at ASC LIMIT 50");
        $stmt->bind_param("i", $last_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        echo json_encode(['messages' => $messages]);
        break;

    case 'users':
        $result = $conn->query("SELECT username FROM chat_users WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY username");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['users' => $users]);
        break;

    case 'change_username':
        if (isset($_POST['username'])) {
            $new_username = trim($_POST['username']);
            if (!empty($new_username) && strlen($new_username) <= 50) {
                $_SESSION['chat_username'] = $new_username;
                echo json_encode(['success' => true, 'username' => $new_username]);
            } else {
                echo json_encode(['success' => false]);
            }
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>