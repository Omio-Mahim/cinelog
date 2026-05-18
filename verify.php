<?php
// ============================================================
// verify.php - Email Verification Page
// ============================================================
session_start();
require_once 'db.php';

$message = '';
$type    = 'info';

if (isset($_GET['token']) && !empty($_GET['token'])) {

    $token = htmlspecialchars(trim($_GET['token']));

    $query = mysqli_prepare($conn,
        "SELECT id, username FROM users WHERE verification_token = ? AND is_verified = 0"
    );
    mysqli_stmt_bind_param($query, "s", $token);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        $update = mysqli_prepare($conn,
            "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?"
        );
        mysqli_stmt_bind_param($update, "i", $user['id']);
        mysqli_stmt_execute($update);

        if (mysqli_stmt_affected_rows($update) > 0) {
            $action = "Verified email address";
            $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
            mysqli_stmt_bind_param($log, "is", $user['id'], $action);
            mysqli_stmt_execute($log);

            $message = "Your account has been verified, " . htmlspecialchars($user['username']) . "! You can now log in.";
            $type    = 'success';
        } else {
            $message = "Something went wrong. Please try again.";
            $type    = 'error';
        }
    } else {
        $message = "This verification link is invalid or has already been used.";
        $type    = 'error';
    }
} else {
    $message = "No verification token found. Please check your email link.";
    $type    = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card text-center">
        <div style="font-size:4rem;margin-bottom:var(--space-md);">
            <?php echo $type === 'success' ? '✅' : '❌'; ?>
        </div>
        <h2>Account Verification</h2>

        <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>

        <?php if ($type === 'success'): ?>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-secondary">Back to Register</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>