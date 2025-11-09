<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - AlfahrelRifananda</title>
    <link rel="alternate" type="application/rss+xml" title="Blog RSS Feed" href="rss.php">
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
    
    <h1>Blog</h1>
    <p><a href="rss.php">RSS Feed</a></p>
    
    <hr>
    
    <?php
    $conn = getConnection();
    
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT * FROM posts WHERE id=$id");
        $post = $result->fetch_assoc();
        
        if ($post):
    ?>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><small>By <?php echo htmlspecialchars($post['author']); ?> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></small></p>
        <hr>
        <?php if ($post['image']): ?>
            <p><img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width: 90vw;"></p>
        <?php endif; ?>
        <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        <hr>
        <p><a href="blog.php">Back to Blog</a></p>
    <?php
        else:
            echo "<p>Post not found.</p>";
        endif;
    } else {
        $posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
        
        if ($posts && $posts->num_rows > 0):
            while ($post = $posts->fetch_assoc()):
    ?>
        <h2><a href="blog.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
        <p><small>By <?php echo htmlspecialchars($post['author']); ?> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></small></p>
        <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...</p>
        <p><a href="blog.php?id=<?php echo $post['id']; ?>">Read more</a></p>
    <?php
            endwhile;
        else:
    ?>
        <p>No blog posts yet. Check back soon!</p>
    <?php
        endif;
    }
    $conn->close();
    ?>
    
     <footer> 
        <hr>
        <p><a href="projects.php">View All Projects</a> | <a href="contact.php">Get In Touch</a></p>    
        © 2022 AlfahrelRifananda
    </footer>
</body>
</html>
