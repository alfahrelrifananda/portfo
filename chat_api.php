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
        if (isset($_POST['message'])) {
            $message = trim($_POST['message']);
            if (!empty($message)) {
                $stmt = $conn->prepare("INSERT INTO chat_messages (username, message) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $message);
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