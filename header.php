<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'AlfahrelRifananda' ?></title>
</head>
<body>
    <nav>
        <a href="index.php">Home</a> | 
        <a href="about.php">About</a> | 
        <a href="projects.php">Projects</a> | 
        <a href="blog.php">Blog</a> | 
        <a href="contact.php">Contact</a>
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
            | <a href="dashboard.php">Dashboard</a>
        <?php endif; ?>
    </nav>
    <hr>