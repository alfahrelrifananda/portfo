<?php
require_once 'config.php';
$title = 'Home - AlfahrelRifananda';
include 'header.php';
?>

<h1>My Projects</h1>
    
    <?php
    $conn = getConnection();
    $projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC");
    
    if ($projects && $projects->num_rows > 0):
        while ($project = $projects->fetch_assoc()):
    ?>
        <h2><?php echo htmlspecialchars($project['title']); ?></h2>
        <?php if ($project['image']): ?>
            <p><img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" width="400"/></p>
        <?php endif; ?>
        <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
        <?php if ($project['url']): ?>
            <p><a href="<?php echo htmlspecialchars($project['url']); ?>" target="_blank">View Project</a></p>
        <?php endif; ?>
        <p><small>Added on <?php echo date('F j, Y', strtotime($project['created_at'])); ?></small></p>
    <?php
        endwhile;
    else:
    ?>
        <p>No projects available yet. Check back soon!</p>
    <?php endif; $conn->close(); ?>

<?php include 'footer.php'; ?>
