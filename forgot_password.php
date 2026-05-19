<?php

session_start();
require_once 'db.php';
require_once 'mailer.php';
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$type    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $type    = 'error';
    } else {
        // Always show the same message for security (prevents email enumeration)
        $message = "If that email is registered, you will receive a reset link shortly.";
        $type    = 'info';

        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $token = bin2hex(random_bytes(32));

            // Remove old tokens for this email
            $del = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
            mysqli_stmt_bind_param($del, "s", $email);
            mysqli_stmt_execute($del);

            // Store new token
            $ins = mysqli_prepare($conn, "INSERT INTO password_resets (email, token) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, "ss", $email, $token);
            mysqli_stmt_execute($ins);

            $reset_link = SITE_URL . "/reset_password.php?token=" . $token;
            $email_body = "
                <h2>Password Reset Request</h2>
                <p>Click the link below to set a new CineLog password. This link expires in 1 hour.</p>
                <p><a href='{$reset_link}' style='color:#e50914;font-size:16px;'>🔑 Reset My Password</a></p>
                <p style='color:#999;font-size:13px;'>If you didn't request this, simply ignore this email.</p>
            ";
            send_email($email, "CineLog Password Reset", $email_body);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card">
        <h2>Forgot Password?</h2>
        <p class="text-muted mb-md">Enter your email and we'll send you a reset link.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Your Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com"
                       autocomplete="email">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>

        <hr class="divider">
        <p class="text-muted text-center">
            <a href="login.php">← Back to Login</a>
        </p>
    </div>
</div>

</body>
</html>