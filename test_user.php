<?php
require_once 'config.php';

$conn = getConnection();

$result = $conn->query("SELECT * FROM admin_users");

$username = $_ENV['ADMIN_TEST_USERNAME'];
$password = $_ENV['ADMIN_TEST_PASSWORD_2'];

$stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "<h3>User found</h3>";
    
    if (password_verify($password, $user['password'])) {
        echo "<strong style='color:green;'>Password verification: SUCCESS!</strong>";
    }
} else {
    echo "<strong style='color:red;'>User not found!</strong>";
}

$conn->close();
?>