<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$message = '';

if (isset($_POST['create'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $author = $conn->real_escape_string($_POST['author']);
    $image = $conn->real_escape_string($_POST['image']);
    
    $sql = "INSERT INTO posts (title, content, author, image) VALUES ('$title', '$content', '$author', '$image')";
    
    if ($conn->query($sql)) {
        $message = "Post created successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $author = $conn->real_escape_string($_POST['author']);
    $image = $conn->real_escape_string($_POST['image']);
    
    $sql = "UPDATE posts SET title='$title', content='$content', author='$author', image='$image', updated_at=NOW() WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Post updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM posts WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Post deleted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

$editPost = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM posts WHERE id=$id");
    $editPost = $result->fetch_assoc();
}

$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts</title>
</head>
<body>
    <h1>Manage Blog Posts</h1>
    
    <p><a href="dashboard.php">[Back to Dashboard]</a> | <a href="blog.php" target="_blank">[View Blog]</a> | <a href="rss.php" target="_blank">[RSS Feed]</a></p>
    
    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>
    
    <hr>
    
    <h2><?php echo $editPost ? 'Edit Post' : 'Add New Post'; ?></h2>
    <form method="POST" action="">
        <?php if ($editPost): ?>
            <input type="hidden" name="id" value="<?php echo $editPost['id']; ?>">
        <?php endif; ?>
        
        <p>
            <label for="title">Title *</label><br>
            <input type="text" id="title" name="title" 
                   value="<?php echo $editPost ? htmlspecialchars($editPost['title']) : ''; ?>" 
                   required size="60">
        </p>
        
        <p>
            <label for="content">Content *</label><br>
            <textarea id="content" name="content" rows="10" cols="60" required><?php echo $editPost ? htmlspecialchars($editPost['content']) : ''; ?></textarea>
        </p>
        
        <p>
            <label for="author">Author *</label><br>
            <input type="text" id="author" name="author" 
                   value="<?php echo $editPost ? htmlspecialchars($editPost['author']) : ''; ?>" 
                   required size="40">
        </p>
        
        <p>
            <label for="image">Image URL (optional)</label><br>
            <input type="text" id="image" name="image" 
                   value="<?php echo $editPost ? htmlspecialchars($editPost['image']) : ''; ?>" 
                   size="60">
            <br><small>Enter full URL (e.g., https://example.com/image.jpg or /uploads/image.jpg)</small>
        </p>
        
        <p>
            <button type="submit" name="<?php echo $editPost ? 'update' : 'create'; ?>">
                <?php echo $editPost ? 'Update Post' : 'Add Post'; ?>
            </button>
            
            <?php if ($editPost): ?>
                <a href="manage_posts.php"><button type="button">Cancel</button></a>
            <?php endif; ?>
        </p>
    </form>
    
    <hr>
    
    <h2>All Posts</h2>
    <?php if ($posts->num_rows > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Image</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $posts->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo $row['image'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="?edit=<?php echo $row['id']; ?>">[Edit]</a>
                            <a href="?delete=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Are you sure?')">
                                [Delete]
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No posts found.</p>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>