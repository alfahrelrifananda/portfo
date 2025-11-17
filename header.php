<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?= $title ?? 'AlfahrelRifananda' ?></title>
<link rel="icon" href="data:image/svg+xml;utf8,
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'>
      <rect width='100%' height='100%' fill='%23ffffff'/>
      <text x='50%' y='50%' font-family='Arial, sans-serif' font-weight='700' font-size='36'
            fill='%23000000' dominant-baseline='middle' text-anchor='middle'>AR</text>
    </svg>">
</head>
<body>
    <header>
        <a href="/">
        <h1><u>AlfahrelRifananda</u></h1>
        </a>
        <nav>
            <a href="index.php">Beranda</a> | 
            <!-- <a href="about.php">Tentang</a> |  -->
            <a href="projects.php">Proyek</a> | 
            <a href="blog.php">Blog</a> | 
            <a href="contact.php">Kontak</a> |
            <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                | <a href="dashboard.php">Dashboard</a>
            <?php endif; ?>
            <a href="chat.php">Chat</a>
        </nav>
    </header>
    
    <hr>
