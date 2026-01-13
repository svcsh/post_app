<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="dashboard-card">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h2>
        
        <p>You're logged in and can now manage your account and view posts.</p>
        
        <div class="btn-group">
            <a href="index.php" class="btn-nav">View All Posts</a>
            <a href="logout.php" class="btn-nav btn-logout">Logout</a>
        </div>
    </div>
</div>

</body>
</html>
