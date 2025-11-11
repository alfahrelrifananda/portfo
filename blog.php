<?php
require_once 'config.php';
$title = 'Blog - AlfahrelRifananda';
include 'header.php';
?>
<?php
    $conn = getConnection();
    
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT * FROM posts WHERE id=$id");
        $post = $result->fetch_assoc();
        
        if ($post):
    ?>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><b><?php echo date('j F Y', strtotime($post['created_at'])); ?></b></p>
        <hr>
    <div class="blog-container">
        <?php if ($post['image']): ?>
            <p><img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width: 100%;"></p>
        <?php endif; ?>
        <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        </div>
       
    <?php
        else:
            echo "<p>Postingan tidak dapat ditemukan.</p>";
        endif;
    } else {
        $posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
    
        ?>
        <p>Untuk versi rss, silahkan klik <a href="rss.php">disini</a>.</p>
    <hr>
    <?php
        if ($posts && $posts->num_rows > 0):
            while ($post = $posts->fetch_assoc()):
    ?>
        <h2><a href="blog.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
        <p><b><?php echo date('j F Y', strtotime($post['created_at'])); ?></b></p>
        <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>...</p>
        <p><a href="blog.php?id=<?php echo $post['id']; ?>">Baca selengkapnya</a></p>
    <?php
            endwhile;
        else:
    ?>
        <p>Belum ada postingan. Silahkan cek kembali nanti!</p>
    <?php
        endif;
    }
    $conn->close();
    ?>

<?php include 'footer.php'; ?>
