<?php
require_once 'config.php';

$conn = getConnection();

$result = $conn->query("SELECT * FROM admin_users");
echo "<h3>Users in database:</h3>";
while ($row = $result->fetch_assoc()) {
    echo "Username: " . $row['username'] . "<br>";
    echo "Password hash: " . $row['password'] . "<br><br>";
}

$username = 'fahrel';
$password = 'fahrel1234';

$stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "<h3>User found:</h3>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Stored hash: " . $user['password'] . "<br>";
    
    if (password_verify($password, $user['password'])) {
        echo "<strong style='color:green;'>Password verification: SUCCESS!</strong>";
    } else {
        echo "<strong style='color:red;'>Password verification: FAILED!</strong><br>";
        echo "Testing with new hash...<br>";
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        echo "New hash would be: " . $newHash . "<br>";
    }
} else {
    echo "<strong style='color:red;'>User not found!</strong>";
}

$conn->close();
?>