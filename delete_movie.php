<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate the movie ID from the URL
if (!isset($_GET['id']) || (int)$_GET['id'] === 0) {
    header('Location: dashboard.php');
    exit;
}

$movie_id = (int)$_GET['id'];

// --- Fetch the movie title first (for the activity log) ---
// Again, we check user_id so users can only delete their own movies
$fetch = mysqli_prepare($conn,
    "SELECT title FROM movies WHERE id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($fetch, "ii", $movie_id, $user_id);
mysqli_stmt_execute($fetch);
$result = mysqli_stmt_get_result($fetch);

if (mysqli_num_rows($result) === 0) {
    // Movie not found or not owned by this user
    header('Location: dashboard.php');
    exit;
}

$movie = mysqli_fetch_assoc($result);
$movie_title = $movie['title'];

// --- Delete the movie ---
$delete = mysqli_prepare($conn,
    "DELETE FROM movies WHERE id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($delete, "ii", $movie_id, $user_id);
mysqli_stmt_execute($delete);

// --- Log the deletion ---
$action = "Deleted movie: " . $movie_title;
$log = mysqli_prepare($conn,
    "INSERT INTO activity_log (user_id, action) VALUES (?, ?)"
);
mysqli_stmt_bind_param($log, "is", $user_id, $action);
mysqli_stmt_execute($log);

// Redirect back to the dashboard
// We pass a simple message via a URL parameter
header('Location: dashboard.php?deleted=1');
exit;