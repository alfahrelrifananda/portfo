<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();
$message = '';

if (isset($_POST['create'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $url = $conn->real_escape_string($_POST['url']);
    $image = $conn->real_escape_string($_POST['image']);
    
    $sql = "INSERT INTO projects (title, description, url, image) VALUES ('$title', '$description', '$url', '$image')";
    
    if ($conn->query($sql)) {
        $message = "Project created successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $url = $conn->real_escape_string($_POST['url']);
    $image = $conn->real_escape_string($_POST['image']);
    
    $sql = "UPDATE projects SET title='$title', description='$description', url='$url', image='$image' WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Project updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM projects WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Project deleted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

$editProject = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM projects WHERE id=$id");
    $editProject = $result->fetch_assoc();
}

$projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects</title>
</head>
<body>
    <h1>Manage Projects</h1>
    
    <p><a href="dashboard.php">[Back to Dashboard]</a> | <a href="projects.php" target="_blank">[View Projects]</a></p>
    
    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>
    
    <hr>
    
    <h2><?php echo $editProject ? 'Edit Project' : 'Add New Project'; ?></h2>
    <form method="POST" action="">
        <?php if ($editProject): ?>
            <input type="hidden" name="id" value="<?php echo $editProject['id']; ?>">
        <?php endif; ?>
        
        <p>
            <label for="title">Title *</label><br>
            <input type="text" id="title" name="title" 
                   value="<?php echo $editProject ? htmlspecialchars($editProject['title']) : ''; ?>" 
                   required size="60">
        </p>
        
        <p>
            <label for="description">Description *</label><br>
            <textarea id="description" name="description" rows="8" cols="60" required><?php echo $editProject ? htmlspecialchars($editProject['description']) : ''; ?></textarea>
        </p>
        
        <p>
            <label for="url">Project URL (optional)</label><br>
            <input type="text" id="url" name="url" 
                   value="<?php echo $editProject ? htmlspecialchars($editProject['url']) : ''; ?>" 
                   size="60">
        </p>
        
        <p>
            <label for="image">Image URL (optional)</label><br>
            <input type="text" id="image" name="image" 
                   value="<?php echo $editProject ? htmlspecialchars($editProject['image']) : ''; ?>" 
                   size="60">
            <br><small>Enter full URL (e.g., https://example.com/image.jpg or /uploads/image.jpg)</small>
        </p>
        
        <p>
            <button type="submit" name="<?php echo $editProject ? 'update' : 'create'; ?>">
                <?php echo $editProject ? 'Update Project' : 'Add Project'; ?>
            </button>
            
            <?php if ($editProject): ?>
                <a href="manage_projects.php"><button type="button">Cancel</button></a>
            <?php endif; ?>
        </p>
    </form>
    
    <hr>
    
    <h2>All Projects</h2>
    <?php if ($projects->num_rows > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>URL</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $projects->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</td>
                        <td><?php echo $row['url'] ? htmlspecialchars($row['url']) : 'N/A'; ?></td>
                        <td><?php echo $row['image'] ? 'Yes' : 'No'; ?></td>
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
        <p>No projects found.</p>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>