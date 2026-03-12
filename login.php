<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (loginUser($username, $password)) {
        header("Location: notes/");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}

if (isLoggedIn()) {
    header("Location: notes/");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NoteTaking</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body id="auth">
    <div class="container">
        <div class="card">
            <h2>Login</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-field">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="button primary full-width">Login</button>
            </form>

            <p class="helper-text">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
</body>

</html>
