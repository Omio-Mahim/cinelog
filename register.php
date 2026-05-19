<?php

session_start();
require_once 'db.php';
require_once 'mailer.php';
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    // --- Back-end validation ---
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        // Check if username or email is already taken
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username or email already taken. Please choose another.";
        } else {

            // Hash the password — NEVER store plain text!
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Generate a unique verification token
            $token = bin2hex(random_bytes(32));

            // Insert the new user
            $insert = mysqli_prepare($conn,
                "INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($insert, "ssss", $username, $email, $hashed_password, $token);
            mysqli_stmt_execute($insert);

            if (mysqli_stmt_affected_rows($insert) > 0) {

                $new_user_id = mysqli_insert_id($conn);

                // Log the registration
                $action = "Registered an account";
                $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
                mysqli_stmt_bind_param($log, "is", $new_user_id, $action);
                mysqli_stmt_execute($log);

                // Send verification email
                $verify_link = SITE_URL . "/verify.php?token=" . $token;
                $email_body  = "
                    <h2>Welcome to CineLog, {$username}!</h2>
                    <p>Click the link below to verify your email address:</p>
                    <p><a href='{$verify_link}' style='color:#e50914;font-size:16px;'>✅ Verify My Account</a></p>
                    <p style='color:#999;font-size:13px;'>If you didn't create this account, ignore this email.</p>
                ";

                $mail_sent = send_email($email, "Verify Your CineLog Account", $email_body);

                $success = $mail_sent
                    ? "Account created! Please check your email to verify your account."
                    : "Account created, but we couldn't send a verification email. Please contact support.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card">
        <h2 class="text-center">Create an Account</h2>
        <p class="text-muted text-center mb-md">Join CineLog and start tracking your movies!</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" class="btn btn-primary btn-block">Go to Login</a>
        <?php else: ?>

        <form method="POST" action="register.php" onsubmit="return validateRegisterForm()" novalidate>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="e.g. moviefan99"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       autocomplete="username">
            </div>

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
                       placeholder="At least 6 characters"
                       autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Re-enter your password"
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <?php endif; ?>

        <hr class="divider">
        <p class="text-muted text-center">
            Already have an account? <a href="login.php">Log in here</a>
        </p>
    </div>
</div>

<script>
    // Simple front-end validation before the form submits to the server
    function validateRegisterForm() {
        var username = document.getElementById('username').value.trim();
        var email    = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var confirm  = document.getElementById('confirm_password').value;

        if (username.length < 3) {
            alert('Username must be at least 3 characters.');
            return false;
        }
        if (email === '') {
            alert('Please enter your email address.');
            return false;
        }
        if (password.length < 6) {
            alert('Password must be at least 6 characters.');
            return false;
        }
        if (password !== confirm) {
            alert('Passwords do not match!');
            return false;
        }
        return true; // Allow form to submit
    }
</script>

</body>
</html>