<?php
include "db.php";
include "Validator.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    die("Invalid post ID!");
}

try {
    // PDO Prepared Statement to fetch post
    $stmt = $conn->prepare("SELECT id, title, content, user_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        die("Post not found!");
    }

    // Check if user owns the post or is admin
    $isOwner = ($post['user_id'] == $_SESSION['user_id']);
    $isAdmin = ($_SESSION['role'] === 'admin');

    if (!$isOwner && !$isAdmin) {
        die("You don't have permission to edit this post!");
    }

    // Handle form submission
    $error = "";
    $success = "";

    if (isset($_POST['update'])) {
        // Sanitize input
        $title = Validator::sanitize($_POST['title']);
        $content = Validator::sanitize($_POST['content']);
        
        // Server-side validation
        if (!Validator::validateTitle($title)) {
            $error = "Title must be between 3 and 255 characters!";
        } elseif (!Validator::validateContent($content)) {
            $error = "Content must be at least 10 characters!";
        } else {
            try {
                // PDO Prepared Statement for update
                $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
                
                if ($stmt->execute([$title, $content, $post_id])) {
                    $success = "Post updated successfully!";
                    $post['title'] = $title;
                    $post['content'] = $content;
                } else {
                    $error = "Failed to update post!";
                }
            } catch (PDOException $e) {
                error_log("Update post error: " . $e->getMessage());
                $error = "An error occurred while updating the post.";
            }
        }
    }

    // Handle delete
    if (isset($_POST['delete'])) {
        try {
            // PDO Prepared Statement for delete
            $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
            
            if ($stmt->execute([$post_id])) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Failed to delete post!";
            }
        } catch (PDOException $e) {
            error_log("Delete post error: " . $e->getMessage());
            $error = "An error occurred while deleting the post.";
        }
    }

} catch (PDOException $e) {
    error_log("Edit post error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
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
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="edit-card">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required 
                       minlength="3" maxlength="255">
                <small class="form-hint">3-255 characters</small>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="10" required minlength="10"><?php echo htmlspecialchars($post['content']); ?></textarea>
                <small class="form-hint">Minimum 10 characters</small>
            </div>
            <div class="button-group">
                <button type="submit" name="update" class="btn-primary">Update Post</button>
                <button type="submit" name="delete" class="btn-danger" onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">Delete Post</button>
            </div>
        </form>
    </div>

</div>

<script>
    // Client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        
        if (!title || !content) {
            e.preventDefault();
            alert('Please fill in all fields!');
            return false;
        }
        
        if (title.length < 3 || title.length > 255) {
            e.preventDefault();
            alert('Title must be between 3 and 255 characters!');
            return false;
        }
        
        if (content.length < 10) {
            e.preventDefault();
            alert('Content must be at least 10 characters!');
            return false;
        }
    });
</script>

</body>
</html>
