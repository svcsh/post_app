<?php
include "db.php";
include "Validator.php";

$error = "";

if (isset($_POST['login'])) {
    // Sanitize input
    $email = Validator::sanitize($_POST['email']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Server-side validation
    if (!Validator::validateEmail($email)) {
        $error = "Please enter a valid email address!";
    } elseif (empty($password)) {
        $error = "Password is required!";
    } else {
        try {
            // PDO Prepared Statement
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password!";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>🔐 Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <small class="form-hint">Valid email format required (e.g., user@example.com)</small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <small class="form-hint">Case sensitive</small>
            </div>
            <button type="submit" name="login" class="btn-primary">Login</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
</div>

<script>
    // Client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in all fields!');
            return false;
        }
        
        // Email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address!');
            return false;
        }
    });
</script>

</body>
</html>

