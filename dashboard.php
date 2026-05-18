<?php
// ============================================================
// dashboard.php - User's Main Dashboard
// ============================================================
session_start();
require_once 'db.php';

// Protect page: redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info (to check Pro status)
$user_query = mysqli_prepare($conn, "SELECT username, is_pro FROM users WHERE id = ?");
mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($user_query));

// Fetch all movies for this user, newest first
$movies_query = mysqli_prepare($conn,
    "SELECT * FROM movies WHERE user_id = ? ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($movies_query, "i", $user_id);
mysqli_stmt_execute($movies_query);
$movies_result = mysqli_stmt_get_result($movies_query);
$total_movies  = mysqli_num_rows($movies_result);

// Calculate average rating
$avg_query = mysqli_prepare($conn,
    "SELECT AVG(rating) as avg_rating FROM movies WHERE user_id = ?"
);
mysqli_stmt_bind_param($avg_query, "i", $user_id);
mysqli_stmt_execute($avg_query);
$avg_row    = mysqli_fetch_assoc(mysqli_stmt_get_result($avg_query));
$avg_rating = $avg_row['avg_rating'] ? round($avg_row['avg_rating'], 1) : '—';

// Show "deleted" message if coming from delete_movie.php
$deleted_msg = isset($_GET['deleted']) ? "Movie entry deleted successfully." : '';

// Converts a numeric rating into filled/empty star characters
function get_stars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= ($i <= $rating) ? '★' : '☆';
    }
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="main-container">

    <!-- Page header row: title on left, button on right -->
    <div class="page-header">
        <div class="page-header-text">
            <h1>
                My Movie Diary
                <?php if ($user['is_pro']): ?>
                    <span class="pro-badge">PRO</span>
                <?php endif; ?>
            </h1>
            <p class="text-muted">
                Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </p>
        </div>
        <a href="add_movie.php" class="btn btn-primary">+ Log a Movie</a>
    </div>

    <!-- Deletion success message -->
    <?php if ($deleted_msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($deleted_msg); ?></div>
    <?php endif; ?>

    <!-- Stats Bar -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="stat-number"><?php echo $total_movies; ?></div>
            <div class="stat-label">Movies Logged</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo $avg_rating; ?></div>
            <div class="stat-label">Avg. Rating</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" style="color: var(--star-color);">★</div>
            <div class="stat-label">Your Diary</div>
        </div>
    </div>

    <!-- Movie Grid or Empty State -->
    <?php if ($total_movies === 0): ?>
        <div class="card empty-state">
            <div class="empty-icon">🎬</div>
            <h3>No movies logged yet!</h3>
            <p class="text-muted">Click the button below to start your movie diary.</p>
            <a href="add_movie.php" class="btn btn-primary mt-md">Log Your First Movie</a>
        </div>
    <?php else: ?>
        <div class="movie-grid">
            <?php while ($movie = mysqli_fetch_assoc($movies_result)): ?>
            <div class="movie-card">

                <h3><?php echo htmlspecialchars($movie['title']); ?></h3>

                <div class="stars"><?php echo get_stars($movie['rating']); ?></div>

                <?php if (!empty($movie['review'])): ?>
                    <p class="review-text">
                        <?php echo htmlspecialchars($movie['review']); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($movie['watched_date'])): ?>
                    <p class="movie-date">
                        📅 <?php echo htmlspecialchars($movie['watched_date']); ?>
                    </p>
                <?php endif; ?>

                <div class="card-actions">
                    <a href="edit_movie.php?id=<?php echo $movie['id']; ?>"
                       class="btn btn-secondary btn-sm">✏️ Edit</a>
                    <a href="delete_movie.php?id=<?php echo $movie['id']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this entry? This cannot be undone.');">
                        🗑️ Delete
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Scroll to top button -->
<button class="scroll-top-btn" id="scroll-top-btn" aria-label="Scroll to top">↑</button>

</body>
</html>