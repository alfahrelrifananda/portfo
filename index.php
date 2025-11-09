<?php
include 'config.php';
$title = 'Home - AlfahrelRifananda';
include 'header.php';
?>

<h1>Welcome to My Site</h1>
<p>Hello! my name is Fahrel, and I'm a web developer and GNU/LINUX Advocate.</p>
<p>Sadly, this site is running through Cloudflare Tunnel because I'm using a VPS and don’t have a static IP.</p>

<hr>

    <p><a href="rss.php">RSS Feed</a></p>
    
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

<?php include 'footer.php'; ?>
