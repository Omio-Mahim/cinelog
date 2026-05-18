<?php
// ============================================================
// edit_movie.php - Edit a Movie Entry (UPDATE)
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Validate movie ID from URL
if (!isset($_GET['id']) || (int)$_GET['id'] === 0) {
    header('Location: dashboard.php');
    exit;
}
$movie_id = (int)$_GET['id'];

// Fetch the movie — check it belongs to this user
$fetch = mysqli_prepare($conn, "SELECT * FROM movies WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($fetch, "ii", $movie_id, $user_id);
mysqli_stmt_execute($fetch);
$result = mysqli_stmt_get_result($fetch);

if (mysqli_num_rows($result) === 0) {
    header('Location: dashboard.php');
    exit;
}
$movie = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title        = trim($_POST['title']);
    $rating       = (int)$_POST['rating'];
    $review       = trim($_POST['review']);
    $watched_date = trim($_POST['watched_date']);

    if (empty($title)) {
        $error = "Movie title is required.";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } else {

        $update = mysqli_prepare($conn,
            "UPDATE movies SET title = ?, rating = ?, review = ?, watched_date = ? WHERE id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($update, "sissii", $title, $rating, $review, $watched_date, $movie_id, $user_id);
        mysqli_stmt_execute($update);

        // Log even if no rows changed (user may submit same data)
        $action = "Edited movie: " . $title;
        $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
        mysqli_stmt_bind_param($log, "is", $user_id, $action);
        mysqli_stmt_execute($log);

        $success = "Movie updated successfully!";

        // Update the local $movie array so the form shows fresh data
        $movie['title']        = $title;
        $movie['rating']       = $rating;
        $movie['review']       = $review;
        $movie['watched_date'] = $watched_date;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card">
        <h2>✏️ Edit Movie</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_movie.php?id=<?php echo $movie_id; ?>">

            <div class="form-group">
                <label for="title">Movie Title *</label>
                <input type="text" id="title" name="title"
                       value="<?php echo htmlspecialchars($movie['title']); ?>">
            </div>

            <div class="form-group">
                <label for="rating">Your Rating *</label>
                <select id="rating" name="rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>"
                            <?php echo ($movie['rating'] == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="review">Your Review</label>
                <textarea id="review" name="review"><?php echo htmlspecialchars($movie['review']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="watched_date">Date Watched</label>
                <input type="date" id="watched_date" name="watched_date"
                       value="<?php echo htmlspecialchars($movie['watched_date']); ?>">
            </div>

            <div class="flex gap-sm flex-wrap">
                <button type="submit" class="btn btn-primary">Update Movie</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>