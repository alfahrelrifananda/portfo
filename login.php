<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        }
    }
    
    $error = "Username atau password salah.";
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>
<body>
    <h1>Admin Login</h1>
    
    <?php if ($error): ?>
        <p><strong><?php echo $error; ?></strong></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <p>
            <label for="username">Username</label><br>
            <input type="text" id="username" name="username" required size="30">
        </p>
        
        <p>
            <label for="password">Password</label><br>
            <input type="password" id="password" name="password" required size="30">
        </p>
        
        <p>
            <button type="submit">Login</button>
        </p>
    </form>
    
    <hr>
    <p><a href="index.php">Kembali ke Beranda</a></p>
</body>
</html>