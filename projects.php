<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - AlfahrelRifananda</title>
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
    
    <h1>My Projects</h1>
    
    <?php
    $conn = getConnection();
    $projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC");
    
    if ($projects && $projects->num_rows > 0):
        while ($project = $projects->fetch_assoc()):
    ?>
        <h2><?php echo htmlspecialchars($project['title']); ?></h2>
        <?php if ($project['image']): ?>
            <p><img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" width="400"></p>
        <?php endif; ?>
        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
        <?php if ($project['url']): ?>
            <p><a href="<?php echo htmlspecialchars($project['url']); ?>" target="_blank">View Project</a></p>
        <?php endif; ?>
        <p><small>Added on <?php echo date('F j, Y', strtotime($project['created_at'])); ?></small></p>
        <hr>
    <?php
        endwhile;
    else:
    ?>
        <p>No projects available yet. Check back soon!</p>
    <?php endif; $conn->close(); ?>
</body>
</html>
