<?php

session_start();

// Save user_id before we destroy the session (for logging)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id) {
    // Log the logout action before destroying the session
    require_once 'db.php';
    $action = "Logged out";
    $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
    mysqli_stmt_bind_param($log, "is", $user_id, $action);
    mysqli_stmt_execute($log);
}

// session_unset() clears all session variables
session_unset();

// session_destroy() completely removes the session from the server
session_destroy();

// Send the user back to the login page
header('Location: login.php');
exit;