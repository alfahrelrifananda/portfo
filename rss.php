<?php
require_once 'config.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$conn = getConnection();
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 20");

$blogTitle = "AlfahrelRifananda's Blog";
$blogDescription = "Latest news from AlfahrelRifananda's blog posts";
$blogLink = "https://alfahrelrifananda.my.id/blog.php";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?php echo htmlspecialchars($blogTitle); ?></title>
    <link><?php echo htmlspecialchars($blogLink); ?></link>
    <description><?php echo htmlspecialchars($blogDescription); ?></description>
    <language>en-us</language>
    <atom:link href="http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" rel="self" type="application/rss+xml" />
    
    <?php while ($post = $posts->fetch_assoc()): ?>
    <item>
      <title><?php echo htmlspecialchars($post['title']); ?></title>
      <link><?php echo $blogLink . '?id=' . $post['id']; ?></link>
      <description><![CDATA[
        <?php if ($post['image']): ?>
          <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" /><br/><br/>
        <?php endif; ?>
        <?php echo $post['content']; ?>
      ]]></description>
      <author><?php echo htmlspecialchars($post['author']); ?></author>
      <pubDate><?php echo date('r', strtotime($post['created_at'])); ?></pubDate>
      <guid><?php echo $blogLink . '?id=' . $post['id']; ?></guid>
      <?php if ($post['image']): ?>
      <enclosure url="<?php echo htmlspecialchars($post['image']); ?>" type="image/jpeg" />
      <?php endif; ?>
    </item>
    <?php endwhile; ?>
    
  </channel>
</rss>
<?php
$conn->close();
?>
