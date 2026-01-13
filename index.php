<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// SEARCH
$search = $_GET['search'] ?? "";

// PAGINATION
$limit = 5;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

// COUNT POSTS
$countSql = "SELECT COUNT(*) AS total FROM posts
             WHERE title LIKE ? OR content LIKE ?";
$stmt = mysqli_prepare($conn, $countSql);
$like = "%$search%";
mysqli_stmt_bind_param($stmt, "ss", $like, $like);
mysqli_stmt_execute($stmt);
$totalResult = mysqli_stmt_get_result($stmt);
$totalPosts = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalPosts / $limit);

// FETCH POSTS
$sql = "SELECT * FROM posts
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssii", $like, $like, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
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
            <a href="logout.php" class="btn-nav btn-logout">Logout</a>
        </div>
    </div>

    <!-- SEARCH FORM -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search posts..."
               value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-primary">Search</button>
    </form>

    <!-- POSTS -->
    <div class="posts-section">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="post">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <small class="post-meta">📅 Posted on <?php echo htmlspecialchars($row['created_at']); ?></small>
                    <div class="post-actions">
                        <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-small">✏️ Edit</a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>&delete=1" class="btn-small btn-small-danger" onclick="return confirm('Are you sure you want to delete this post?');">🗑️ Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-posts"><p>No posts found.</p></div>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <nav class="pagination-nav">
        <ul class="pagination-list">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="<?php if ($i == $page) echo 'active'; ?>">
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

</div>

</body>
</html>
