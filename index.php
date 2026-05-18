<?php
// ============================================================
// index.php - Homepage / Landing Page
// ============================================================
session_start();

// If already logged in, skip the landing page and go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- viewport meta tag is CRITICAL for responsive design on phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineLog - Your Movie Diary</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <h1>🎬 CineLog</h1>
    <p>Your personal movie diary. Track what you've watched, rate films, and write reviews — all in one place.</p>

    <div class="hero-buttons">
        <a href="register.php" class="btn btn-primary btn-lg">Get Started Free</a>
        <a href="login.php"    class="btn btn-outline btn-lg">Log In</a>
    </div>

    <!-- Feature highlights grid -->
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">📽️</div>
            <h3>Log Movies</h3>
            <p class="text-muted">Keep a diary of every film you've seen with ratings and reviews.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⭐</div>
            <h3>Rate & Review</h3>
            <p class="text-muted">Give star ratings and write your personal thoughts on each film.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Track Stats</h3>
            <p class="text-muted">See how many movies you've watched and your average rating.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔒</div>
            <h3>Private & Safe</h3>
            <p class="text-muted">Your diary is only visible to you, fully secured.</p>
        </div>
    </div>
</section>

<!-- Scroll to top button -->
<button class="scroll-top-btn" id="scroll-top-btn" aria-label="Scroll to top">↑</button>

</body>
</html>