<?php
// ============================================================
// profile.php - User Profile Page
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// Fetch user data
$user_query = mysqli_prepare($conn, "SELECT username, email, is_pro, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($user_query));

// Handle profile info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email    = trim($_POST['email']);

    if (empty($new_username) || empty($new_email)) {
        $error = "Username and email cannot be empty.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Make sure no OTHER user has that username/email
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        mysqli_stmt_bind_param($check, "ssi", $new_username, $new_email, $user_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "That username or email is already in use.";
        } else {
            $upd = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ssi", $new_username, $new_email, $user_id);
            mysqli_stmt_execute($upd);

            $_SESSION['username'] = $new_username;
            $user['username']     = $new_username;
            $user['email']        = $new_email;

            $action = "Updated profile information";
            $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
            mysqli_stmt_bind_param($log, "is", $user_id, $action);
            mysqli_stmt_execute($log);

            $success = "Profile updated successfully!";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass     = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $pq = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($pq, "i", $user_id);
    mysqli_stmt_execute($pq);
    $pass_row = mysqli_fetch_assoc(mysqli_stmt_get_result($pq));

    if (!password_verify($current_pass, $pass_row['password'])) {
        $error = "Your current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, "si", $new_hash, $user_id);
        mysqli_stmt_execute($upd);

        $action = "Changed account password";
        $log = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
        mysqli_stmt_bind_param($log, "is", $user_id, $action);
        mysqli_stmt_execute($log);

        $success = "Password changed successfully!";
    }
}

// Fetch last 5 activity log entries
$activity_query = mysqli_prepare($conn,
    "SELECT action, logged_at FROM activity_log WHERE user_id = ? ORDER BY logged_at DESC LIMIT 5"
);
mysqli_stmt_bind_param($activity_query, "i", $user_id);
mysqli_stmt_execute($activity_query);
$activity_result = mysqli_stmt_get_result($activity_query);

// Fetch total movie count
$count_q  = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM movies WHERE user_id = ?");
mysqli_stmt_bind_param($count_q, "i", $user_id);
mysqli_stmt_execute($count_q);
$total_movies = mysqli_fetch_assoc(mysqli_stmt_get_result($count_q))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CineLog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="main-container">

    <h1 class="mb-md">
        My Profile
        <?php if ($user['is_pro']): ?>
            <span class="pro-badge">PRO</span>
        <?php endif; ?>
    </h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!--
        profile-grid = 2 columns on tablet/desktop, 1 column on mobile
        (Controlled by CSS media queries in style.css)
    -->
    <div class="profile-grid">

        <!-- LEFT COLUMN -->
        <div>
            <!-- Account Summary -->
            <div class="card">
                <h3>Account Summary</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong>    <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                <p><strong>Movies Logged:</strong> <?php echo $total_movies; ?></p>
                <p>
                    <strong>Account Type:</strong>
                    <?php if ($user['is_pro']): ?>
                        <span class="pro-badge">CineLog PRO</span>
                    <?php else: ?>
                        Free Account
                        &mdash; <a href="upgrade.php">Upgrade ⭐</a>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Edit Profile Form -->
            <div class="card">
                <h3>Edit Profile</h3>
                <form method="POST" action="profile.php">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                               value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <!-- Hidden input identifies which form was submitted -->
                    <input type="hidden" name="update_profile" value="1">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="card">
                <h3>Change Password</h3>
                <form method="POST" action="profile.php">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    <input type="hidden" name="change_password" value="1">
                    <button type="submit" class="btn btn-secondary">Change Password</button>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
            <!-- Activity Log -->
            <div class="card">
                <h3>Recent Activity</h3>
                <p class="text-muted mb-md">Your last 5 actions on CineLog</p>

                <?php if (mysqli_num_rows($activity_result) === 0): ?>
                    <p class="text-muted">No activity recorded yet.</p>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php while ($activity = mysqli_fetch_assoc($activity_result)): ?>
                            <li>
                                <?php echo htmlspecialchars($activity['action']); ?>
                                <span class="activity-time">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['logged_at'])); ?>
                                </span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <h3>Quick Links</h3>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:10px;">
                    <li><a href="add_movie.php">📽️ Log a new movie</a></li>
                    <li><a href="dashboard.php">🎬 View my diary</a></li>
                    <li><a href="upgrade.php">⭐ Upgrade to Pro</a></li>
                    <li><a href="logout.php" style="color:#dc3545;">🚪 Logout</a></li>
                </ul>
            </div>
        </div>

    </div><!-- end .profile-grid -->
</div>

<button class="scroll-top-btn" id="scroll-top-btn" aria-label="Scroll to top">↑</button>

</body>
</html>