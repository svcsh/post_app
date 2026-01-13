<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    die("Post not found!");
}

// Check if user owns the post
if ($post['user_id'] != $_SESSION['user_id']) {
    die("You don't have permission to edit this post!");
}

// Handle form submission
$error = "";
$success = "";

if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $error = "Title and Content cannot be empty!";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE posts SET title=?, content=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $post_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Post updated successfully!";
            $post['title'] = $title;
            $post['content'] = $content;
        } else {
            $error = "Failed to update post!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Failed to delete post!";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="navbar">
        <h1>✏️ Edit Post</h1>
        <div class="nav-links">
            <a href="index.php" class="btn-nav">Back to Posts</a>
            <a href="logout.php" class="btn-nav btn-logout">Logout</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success-msg"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="edit-card">
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            <div class="button-group">
                <button type="submit" name="update" class="btn-primary">Update Post</button>
                <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
