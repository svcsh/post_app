<?php
include "db.php";
include "Validator.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    // Sanitize input
    $username = Validator::sanitize($_POST['username']);
    $email = Validator::sanitize($_POST['email']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Server-side validation
    if (empty($username)) {
        $error = "Username is required!";
    } elseif (!Validator::validateUsername($username)) {
        $error = "Username must be 3-20 characters (letters, numbers, underscore only)!";
    } elseif (!Validator::validateEmail($email)) {
        $error = "Please enter a valid email address!";
    } elseif (!Validator::validatePasswordStrength($password)) {
        $error = "Password must be at least 8 characters with uppercase, lowercase, and number!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username or Email already exists!";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user with role 'user'
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                
                if ($stmt->execute([$username, $email, $hashedPassword])) {
                    $userId = $conn->lastInsertId();
                    
                    // Auto-login the user
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'user';
                    
                    $success = "Account created successfully! Redirecting...";
                    // Redirect after 2 seconds
                    header("refresh:2; url=dashboard.php");
                } else {
                    $error = "Registration failed! Please try again.";
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = "An error occurred during registration. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>📝 Create Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (empty($success)): ?>
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+">
                <small class="form-hint">3-20 characters (letters, numbers, underscore only)</small>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <small class="form-hint">Valid email format required</small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required minlength="8">
                <small class="form-hint">Min 8 characters with uppercase, lowercase, and number</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required minlength="8">
                <small class="form-hint">Must match password above</small>
            </div>
            <button type="submit" name="register" class="btn-primary">Register</button>
        </form>
        <?php endif; ?>
        
        <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<script>
    // Client-side validation
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Check for empty fields
        if (!username || !email || !password || !confirmPassword) {
            e.preventDefault();
            alert('Please fill in all fields!');
            return false;
        }
        
        // Username validation
        if (username.length < 3 || username.length > 20) {
            e.preventDefault();
            alert('Username must be 3-20 characters!');
            return false;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            e.preventDefault();
            alert('Username can only contain letters, numbers, and underscores!');
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address!');
            return false;
        }
        
        // Password strength validation
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters!');
            return false;
        }
        
        if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
            e.preventDefault();
            alert('Password must contain uppercase, lowercase, and number!');
            return false;
        }
        
        // Password match validation
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
    });
</script>

</body>
</html>
