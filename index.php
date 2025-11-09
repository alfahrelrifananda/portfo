<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - AlfahrelRifananda</title>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="projects.php">Projects</a>
        <a href="blog.php">Blog</a>
        <a href="contact.php">Contact</a>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Dashboard</a>
        <?php endif; ?>
    </nav>
    
    <hr>
    
    <h1>Welcome to My Site</h1>
    
    <p>Hello! my name is Fahrel, and I'm a web developer GNU/LINUX Advocate.</p>
    <p>
    Sadly, This site is runnning through cloudflare tunnel, because I'm using a VPS, and I don't have a static IP.
    </p>
    
   
    <footer> 
        <hr>
        <p><a href="projects.php">View All Projects</a> | <a href="contact.php">Get In Touch</a></p>    
        © 2022 AlfahrelRifananda
    </footer>
</body>
</html>
