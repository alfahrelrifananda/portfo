<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! | <a href="logout.php">[Logout]</a></p>
    
    <hr>
    
    <h2>Manage Content</h2>
    
    <ul>
        <li><a href="manage_posts.php">[Manage Blog Posts]</a></li>
        <li><a href="manage_projects.php">[Manage Projects]</a></li>
    </ul>
    
    <hr>
    <p><a href="index.php">[Back to Site]</a></p>
</body>
</html>
