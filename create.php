<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = $success = "";

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id']; // <-- this is where you get the logged-in user

    if (empty($title) || empty($content)) {
        $error = "Title and content cannot be empty!";
    } else {
        $sql = "INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Post created successfully!";
        } else {
            $error = "Failed to create post!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="navbar">
        <h1>✏️ Create Post</h1>
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
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn-nav">View Posts</a>
        </p>
    <?php endif; ?>

    <div class="edit-card">
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="Enter post title" required>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="10" placeholder="Enter post content" required></textarea>
            </div>
            <div class="button-group">
                <button type="submit" name="submit" class="btn-primary">Create Post</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
    </form>
</div>
</body>
</html>
