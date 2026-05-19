<?php

session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please enter your email and password.";
    } else {

        $query = mysqli_prepare($conn, "SELECT id, username, password, is_verified FROM users WHERE email = ?");
        mysqli_stmt_bind_param($query, "s", $email);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                if ($user['is_verified'] == 0) {
                    $error = "Please verify your email before logging in. Check your inbox.";
                } else {
                    // Login successful — store info in session
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];

                    // Log the login action
                    $action = "Logged in";
                    $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
                    mysqli_stmt_bind_param($log, "is", $user['id'], $action);
                    mysqli_stmt_execute($log);

                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $error = "Incorrect email or password.";
            }
        } else {
            $error = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card">
        <h2 class="text-center">Welcome Back</h2>
        <p class="text-muted text-center mb-md">Log in to your CineLog account</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" onsubmit="return validateLoginForm()" novalidate>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Your password"
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <hr class="divider">

        <p class="text-muted text-center">
            <a href="forgot_password.php">Forgot your password?</a>
        </p>
        <p class="text-muted text-center mt-sm">
            No account yet? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<script>
    function validateLoginForm() {
        var email    = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        if (email === '') {
            alert('Please enter your email.');
            return false;
        }
        if (password === '') {
            alert('Please enter your password.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>