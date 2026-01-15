<?php
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
try {
    $stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $user = null;
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
        
        <?php if ($user): ?>
            <div class="user-info">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Role:</strong> 
                    <?php 
                    $roleDisplay = match($user['role']) {
                        'admin' => '👑 Administrator',
                        'editor' => '✏️ Editor',
                        default => '👤 User'
                    };
                    echo htmlspecialchars($roleDisplay);
                    ?>
                </p>
            </div>
        <?php endif; ?>
        
        <p>You're logged in and can now manage your account and view posts.</p>
        
        <div class="btn-group">
            <a href="index.php" class="btn-nav">View All Posts</a>
            <a href="create.php" class="btn-nav">Create New Post</a>
            <?php if (isset($user) && $user['role'] === 'admin'): ?>
                <a href="admin-panel.php" class="btn-nav" style="background-color: #ff9800;">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-nav btn-logout">Logout</a>
        </div>
    </div>
</div>

<style>
    .user-info {
        background-color: #f5f5f5;
        padding: 15px;
        border-radius: 5px;
        margin: 15px 0;
        border-left: 4px solid #2196F3;
    }
    
    .user-info p {
        margin: 8px 0;
        font-size: 15px;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn-nav {
        padding: 10px 15px;
        text-align: center;
        text-decoration: none;
        background-color: #2196F3;
        color: white;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    
    .btn-nav:hover {
        background-color: #0b7dda;
    }
    
    .btn-logout {
        background-color: #f44336;
    }
    
    .btn-logout:hover {
        background-color: #da190b;
    }
</style>

</body>
</html>
