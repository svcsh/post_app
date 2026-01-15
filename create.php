<?php
include "db.php";
include "Validator.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = $success = "";

if (isset($_POST['submit'])) {
   
    $title = Validator::sanitize($_POST['title']);
    $content = Validator::sanitize($_POST['content']);
    $user_id = $_SESSION['user_id'];

  
    if (!Validator::validateTitle($title)) {
        $error = "Title must be between 3 and 255 characters!";
    } elseif (!Validator::validateContent($content)) {
        $error = "Content must be at least 10 characters!";
    } else {
        try {
           
            $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$title, $content, $user_id])) {
                $success = "Post created successfully!";
                
                $title = $content = "";
            } else {
                $error = "Failed to create post!";
            }
        } catch (PDOException $e) {
            error_log("Create post error: " . $e->getMessage());
            $error = "An error occurred while creating the post. Please try again.";
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
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn-nav">View Posts</a>
        </p>
    <?php endif; ?>

    <?php if (empty($success)): ?>
    <div class="edit-card">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" placeholder="Enter post title" required 
                       value="<?php echo htmlspecialchars($title ?? ''); ?>"
                       minlength="3" maxlength="255">
                <small class="form-hint">3-255 characters</small>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="10" placeholder="Enter post content" required 
                          minlength="10"><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                <small class="form-hint">Minimum 10 characters</small>
            </div>
            <div class="button-group">
                <button type="submit" name="submit" class="btn-primary">Create Post</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
    // Client-side validation
    document.querySelector('form')?.addEventListener('submit', function(e) {
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

