<?php

session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error       = '';
$success     = '';
$token       = '';
$valid_token = false;

// Validate the token from the URL
if (isset($_GET['token']) && !empty($_GET['token'])) {

    $token = htmlspecialchars(trim($_GET['token']));

    // Token must exist and be less than 1 hour old
    $check = mysqli_prepare($conn,
        "SELECT email FROM password_resets WHERE token = ? AND TIMESTAMPDIFF(HOUR, created_at, NOW()) < 1"
    );
    mysqli_stmt_bind_param($check, "s", $token);
    mysqli_stmt_execute($check);
    $check_result = mysqli_stmt_get_result($check);

    if (mysqli_num_rows($check_result) === 1) {
        $valid_token = true;
        $reset_row   = mysqli_fetch_assoc($check_result);
        $reset_email = $reset_row['email'];
    } else {
        $error = "This reset link is invalid or has expired. Please request a new one.";
    }
} else {
    $error = "No reset token found. Please use the link from your email.";
}

// Handle the new password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {

    $new_password = $_POST['new_password'];
    $confirm      = $_POST['confirm_password'];

    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
        mysqli_stmt_bind_param($upd, "ss", $new_hash, $reset_email);
        mysqli_stmt_execute($upd);

        // Delete the used token so it can't be reused
        $del = mysqli_prepare($conn, "DELETE FROM password_resets WHERE token = ?");
        mysqli_stmt_bind_param($del, "s", $token);
        mysqli_stmt_execute($del);

        $success     = "Your password has been reset! You can now log in.";
        $valid_token = false; // Hide the form
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card">
        <h2>Reset Your Password</h2>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
                <br><a href="forgot_password.php">Request a new link</a>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <a href="login.php" class="btn btn-primary btn-block">Go to Login</a>
        <?php endif; ?>

        <?php if ($valid_token): ?>
        <p class="text-muted mb-md">Enter your new password below.</p>
        <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" onsubmit="return validateResetForm()">

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password"
                       placeholder="At least 6 characters">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Re-enter new password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>
    </div>
    </div>

    <script>
        // Client-side validation for reset password using same policy as registration
        function validateResetForm() {
            var password = document.getElementById('new_password').value;
            var confirm  = document.getElementById('confirm_password').value;

            var pwdRegex = /^(?=.{6,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).*$/;

            if (!pwdRegex.test(password)) {
                alert('Password must be at least 6 characters and include uppercase, lowercase, a number, and a special character.');
                return false;
            }
            if (password !== confirm) {
                alert('Passwords do not match.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>