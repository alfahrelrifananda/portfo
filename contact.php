<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - My Portfolio</title>
</head>
<body>
    <nav>
        <a href="index.php">[Home]</a>
        <a href="about.php">[About]</a>
        <a href="projects.php">[Projects]</a>
        <a href="blog.php">[Blog]</a>
        <a href="contact.php">[Contact]</a>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">[Dashboard]</a>
        <?php endif; ?>
    </nav>
    
    <hr>
    
    <h1>Get In Touch</h1>
    
    <p>Email: pahrel1234@gmail.com</p>
    <p>GitHub: github.com/alfahrelrifananda</p>
    <hr>
    
    <p><a href="projects.php">[View All Projects]</a> | <a href="contact.php">[Get In Touch]</a></p>
</body>
</html>