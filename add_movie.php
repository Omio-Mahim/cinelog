<?php
// ============================================================
// add_movie.php - Log a New Movie (CREATE)
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

        $insert = mysqli_prepare($conn,
            "INSERT INTO movies (user_id, title, rating, review, watched_date) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($insert, "isiss", $user_id, $title, $rating, $review, $watched_date);
        mysqli_stmt_execute($insert);

        if (mysqli_stmt_affected_rows($insert) > 0) {
            $action = "Logged movie: " . $title;
            $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
            mysqli_stmt_bind_param($log, "is", $user_id, $action);
            mysqli_stmt_execute($log);
            $success = "Movie logged successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log a Movie - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="narrow-container">
    <div class="card">
        <h2>🎬 Log a Movie</h2>
        <p class="text-muted mb-md">Record a movie you've watched.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <div class="flex gap-sm flex-wrap">
                <a href="dashboard.php" class="btn btn-secondary">← Back to Diary</a>
                <a href="add_movie.php" class="btn btn-primary">+ Log Another</a>
            </div>
        <?php else: ?>

        <form method="POST" action="add_movie.php" onsubmit="return validateMovieForm()" novalidate>

            <div class="form-group">
                <label for="title">Movie Title *</label>
                <input type="text" id="title" name="title"
                       placeholder="e.g. The Godfather"
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="rating">Your Rating (1-5 stars) *</label>
                <select id="rating" name="rating">
                    <option value="">-- Select Rating --</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>"
                            <?php echo (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="review">Your Review (optional)</label>
                <textarea id="review" name="review"
                          placeholder="What did you think of it?"><?php echo isset($_POST['review']) ? htmlspecialchars($_POST['review']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="watched_date">Date Watched (optional)</label>
                <input type="date" id="watched_date" name="watched_date"
                       value="<?php echo isset($_POST['watched_date']) ? htmlspecialchars($_POST['watched_date']) : ''; ?>">
            </div>

            <div class="flex gap-sm flex-wrap">
                <button type="submit" class="btn btn-primary">Save Movie</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

<script>
    function validateMovieForm() {
        var title  = document.getElementById('title').value.trim();
        var rating = document.getElementById('rating').value;
        if (title === '') {
            alert('Please enter the movie title.');
            return false;
        }
        if (rating === '') {
            alert('Please select a rating.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>