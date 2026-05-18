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

<!-- 
PART 1: DATABASE SETUP

Open XAMPP → Start Apache & MySQL → Open phpMyAdmin → Click "SQL" tab → Paste and run each block.
Step 1: Create the Database

SQL

CREATE DATABASE cinelog;
USE cinelog;

Step 2: Create the Users Table

SQL

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100) DEFAULT NULL,
    is_pro TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Step 3: Create the Movies Table

SQL

CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    rating TINYINT NOT NULL,
    review TEXT,
    watched_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

Step 4: Create the Activity Log Table

SQL

CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

Step 5: Create the Password Resets Table

SQL

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

PART 2: PHPMAILER SETUP (No Composer Needed)

Step 1: Go to: https://github.com/PHPMailer/PHPMailer

Step 2: Click the green "Code" button → "Download ZIP"

Step 3: Extract the ZIP file. Inside you will find a src folder.

Step 4: Inside your cinelog project folder, create a new folder called phpmailer.

Step 5: Copy only these 3 files from the extracted ZIP's src folder into your cinelog/phpmailer/ folder:

    Exception.php
    PHPMailer.php
    SMTP.php

Step 6: In any PHP file where you need to send email, add these three lines at the top:

PHP

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

Important: You will need a real Gmail account to send emails. In your Gmail account settings, enable 2-Step Verification, then create an App Password (Google Account → Security → App Passwords). Use that 16-character app password in the mailer config — NOT your real Gmail password.
-->