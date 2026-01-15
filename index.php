<?php
include "db.php";
include "Validator.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate and sanitize search parameter
$search = isset($_GET['search']) ? Validator::sanitize($_GET['search']) : "";

$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

try {
    // Check if user is admin to show all posts or only their own
    $isAdmin = ($_SESSION['role'] === 'admin');

    // Build query based on admin status
    if ($isAdmin) {
        // Admin can see all posts
        $countSql = "SELECT COUNT(*) AS total FROM posts WHERE title LIKE ? OR content LIKE ?";
        $dataSql = "SELECT p.id, p.title, p.content, p.created_at, p.user_id, u.username 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.title LIKE ? OR p.content LIKE ? 
                    ORDER BY p.created_at DESC 
                    LIMIT ? OFFSET ?";
    } else {
        // Regular users can see all posts but only edit their own
        $countSql = "SELECT COUNT(*) AS total FROM posts WHERE title LIKE ? OR content LIKE ?";
        $dataSql = "SELECT p.id, p.title, p.content, p.created_at, p.user_id, u.username 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.title LIKE ? OR p.content LIKE ? 
                    ORDER BY p.created_at DESC 
                    LIMIT ? OFFSET ?";
    }

    // PDO Prepared Statement for count
    $likeSearch = "%$search%";
    $stmt = $conn->prepare($countSql);
    $stmt->execute([$likeSearch, $likeSearch]);
    $totalPosts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalPosts / $limit);

    // PDO Prepared Statement for data
    $stmt = $conn->prepare($dataSql);
    $stmt->execute([$likeSearch, $likeSearch, $limit, $offset]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Index page error: " . $e->getMessage());
    $posts = [];
    $totalPages = 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Posts</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="navbar">
        <h1>📝 Posts</h1>
        <div class="nav-links">
            <a href="create.php" class="btn-nav">➕ New Post</a>
            <a href="dashboard.php" class="btn-nav">Dashboard</a>
            <?php if ($isAdmin): ?>
                <span class="admin-badge">👑 Admin</span>
            <?php endif; ?>
            <a href="logout.php" class="btn-nav btn-logout">Logout</a>
        </div>
    </div>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search posts..."
               value="<?php echo htmlspecialchars($search); ?>" maxlength="255">
        <button type="submit" class="btn-primary">Search</button>
    </form>

    <div class="posts-section">
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $row): ?>
                <div class="post">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <small class="post-meta">📅 Posted on <?php echo htmlspecialchars($row['created_at']); ?> by <strong><?php echo htmlspecialchars($row['username']); ?></strong></small>
                    <div class="post-actions">
                        <?php if ($row['user_id'] == $_SESSION['user_id'] || $isAdmin): ?>
                            <a href="edit.php?id=<?php echo (int)$row['id']; ?>" class="btn-small">✏️ Edit</a>
                            <a href="edit.php?id=<?php echo (int)$row['id']; ?>&delete=1" class="btn-small btn-small-danger" onclick="return confirm('Are you sure you want to delete this post?');">🗑️ Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-posts"><p>No posts found.</p></div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="pagination-nav">
        <ul class="pagination-list">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="<?php if ($i == $page) echo 'active'; ?>">
                    <a href="?page=<?php echo (int)$i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo (int)$i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

</div>

</body>
</html>
