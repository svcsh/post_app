<?php
include "db.php";
include "Validator.php";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['role'] !== 'admin') {
        die("Access Denied! Only administrators can access this page.");
    }
} catch (PDOException $e) {
    error_log("Admin panel error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}

$error = "";
$success = "";

// Handle role update
if (isset($_POST['update_role'])) {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $newRole = isset($_POST['role']) ? $_POST['role'] : '';

    if ($userId <= 0 || !Validator::validateRole($newRole)) {
        $error = "Invalid user ID or role!";
    } elseif ($userId === $_SESSION['user_id'] && $newRole !== 'admin') {
        $error = "You cannot change your own admin role!";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$newRole, $userId])) {
                $success = "User role updated successfully!";
            } else {
                $error = "Failed to update user role!";
            }
        } catch (PDOException $e) {
            error_log("Role update error: " . $e->getMessage());
            $error = "An error occurred while updating the role.";
        }
    }
}

// Fetch all users
try {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch users error: " . $e->getMessage());
    $users = [];
}

// Get statistics
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts");
    $stmt->execute();
    $totalPosts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $stmt->execute();
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    error_log("Statistics error: " . $e->getMessage());
    $totalUsers = $totalPosts = $totalAdmins = 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-panel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2196F3;
            padding-bottom: 15px;
        }

        .admin-header h1 {
            color: #333;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #2196F3;
            margin: 10px 0;
        }

        .users-table-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background-color: #2196F3;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }

        .users-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        .users-table tr:hover {
            background-color: #f9f9f9;
        }

        .role-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-update {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-update:hover {
            background-color: #45a049;
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .role-admin {
            background-color: #FFE0B2;
            color: #E65100;
        }

        .role-editor {
            background-color: #C8E6C9;
            color: #2E7D32;
        }

        .role-user {
            background-color: #BBDEFB;
            color: #1565C0;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2196F3;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="admin-panel">
    <div class="admin-header">
        <h1>Administration Panel</h1>
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="stats-container">
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="number"><?php echo (int)$totalUsers; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Posts</h3>
            <div class="number"><?php echo (int)$totalPosts; ?></div>
        </div>
        <div class="stat-card">
            <h3>Administrators</h3>
            <div class="number"><?php echo (int)$totalAdmins; ?></div>
        </div>
    </div>

    <h2 style="margin-top: 30px; margin-bottom: 15px;">Manage Users</h2>

    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?php echo (int)$row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo htmlspecialchars($row['role']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($row['role'])); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline-flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$row['id']; ?>">
                                    <select name="role" class="role-select">
                                        <option value="user" <?php echo $row['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="editor" <?php echo $row['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                        <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="update_role" class="btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #E3F2FD; border-radius: 5px; border-left: 4px solid #2196F3;">
        <h3 style="margin-top: 0;">Available Roles:</h3>
        <ul style="margin: 10px 0;">
            <li><strong>Admin:</strong> Full access to all features and user management</li>
            <li><strong>Editor:</strong> Can create and edit posts</li>
            <li><strong>User:</strong> Can only view and create their own posts (default)</li>
        </ul>
    </div>
</div>

</body>
</html>
