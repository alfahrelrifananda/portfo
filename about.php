<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - My Portfolio</title>
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
    
    <h1>About Me</h1>
    
    <h2>Who Am I?</h2>
    <p>My name is Fahrel, I'm 20 years old \ male live in Indonesia.</p>
    
    <h2>Skills</h2>
    <ul>
        <li>PHP & MySQL</li>
        <li>HTML & CSS</li>
        <li>JavaScript</li>
        <li>Web Development</li>
        <li>Database Design</li>
    </ul>
    
    <h2>Experience</h2>
    <p>Ive got some Experience in Web Development, as a WordPress Developer back in 2024 when I was doing my intern.</p>
    
    <hr>
    <p><a href="projects.php">View My Work</a> | <a href="contact.php">Contact Me</a></p>
</body>
</html>
