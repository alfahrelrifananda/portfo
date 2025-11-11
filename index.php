<?php
include 'config.php';
$title = 'Home - AlfahrelRifananda';
include 'header.php';
?>

<h1>Selamat datang!</h1>
<p>Halo! perkenalkan saya Fahrel, dan saya adalah seorang web developer dan GNU/LINUX Advocate.</p>

<hr>

    <h2>Postingan terbaru</h2>
    <p>Untuk versi rss blog ini, silahkan klik <a href="rss.php">disini</a>.</p>
    
    <?php
    $conn = getConnection();
    
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $result = $conn->query("SELECT * FROM posts WHERE id=$id");
        $post = $result->fetch_assoc();
        
        if ($post):
    ?>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><small>Oleh <?php echo htmlspecialchars($post['author']); ?> pada tanggal <?php echo date('j F Y', strtotime($post['created_at'])); ?></small></p>
        <hr>
        <?php if ($post['image']): ?>
            <p><img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width: 90vw;"></p>
        <?php endif; ?>
        <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        <hr>
        <p><a href="blog.php">Kembali ke Blog</a></p>
    <?php
        else:
            echo "<p>postingan tidak dapat ditemukan.</p>";
        endif;
      } else {
        $posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
        
        if ($posts && $posts->num_rows > 0):
            while ($post = $posts->fetch_assoc()):
    ?>
        <h2><a href="blog.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
        <p><small>Oleh <?php echo htmlspecialchars($post['author']); ?> pada tanggal <?php echo date('j F Y', strtotime($post['created_at'])); ?></small></p>
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
    <a href="blog.php">Lihat semua postingan</a>

<?php include 'footer.php'; ?>
