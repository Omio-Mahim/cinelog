<?php
// ============================================================
// navbar.php - Shared Navigation Bar
// Every page includes this file with: require 'navbar.php';
// This way, if you update the nav, ALL pages update at once.
// ============================================================

// We need the session to check if the user is logged in.
// session_start() is safe to call multiple times — it only
// starts if a session isn't already running.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in by looking for user_id in session
$is_logged_in = isset($_SESSION['user_id']);

// Figure out which page we're on so we can highlight the active link
// basename(__FILE__) would give navbar.php, so we use the calling file
// We get the current script name from the global server variable
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- ======================================================
     NAVIGATION BAR
     The navbar is sticky (stays at top when scrolling).
     On mobile it collapses into a hamburger menu.
     ====================================================== -->
<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="navbar-inner">

        <!-- Brand / Logo — always visible -->
        <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'index.php'; ?>"
           class="brand" aria-label="CineLog Home">
            🎬 CineLog
        </a>

        <!-- Hamburger Button — only visible on mobile (CSS shows/hides it) -->
        <!-- aria-expanded and aria-controls help screen readers -->
        <button class="nav-hamburger"
                id="nav-hamburger"
                aria-expanded="false"
                aria-controls="navbar-links"
                aria-label="Toggle navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Links -->
        <ul class="navbar-links" id="navbar-links" role="list">

            <?php if ($is_logged_in): ?>
                <!-- Links shown ONLY when logged in -->
                <li>
                    <a href="dashboard.php"
                       <?php echo $current_page === 'dashboard.php' ? 'aria-current="page"' : ''; ?>>
                        🎬 My Movies
                    </a>
                </li>
                <li>
                    <a href="profile.php"
                       <?php echo $current_page === 'profile.php' ? 'aria-current="page"' : ''; ?>>
                        👤 Profile
                    </a>
                </li>
                <li>
                    <a href="upgrade.php"
                       <?php echo $current_page === 'upgrade.php' ? 'aria-current="page"' : ''; ?>>
                        ⭐ Go Pro
                    </a>
                </li>
                <li>
                    <a href="logout.php">🚪 Logout</a>
                </li>

            <?php else: ?>
                <!-- Links shown ONLY when logged out -->
                <li>
                    <a href="login.php"
                       <?php echo $current_page === 'login.php' ? 'aria-current="page"' : ''; ?>>
                        Login
                    </a>
                </li>
                <li>
                    <a href="register.php"
                       <?php echo $current_page === 'register.php' ? 'aria-current="page"' : ''; ?>>
                        Register
                    </a>
                </li>
            <?php endif; ?>

            <!-- Dark/Light mode toggle — always visible -->
            <li>
                <button class="theme-toggle-btn"
                        id="theme-toggle"
                        onclick="toggleTheme()"
                        aria-label="Toggle dark or light mode">
                    🌙 Dark
                </button>
            </li>

        </ul><!-- end .navbar-links -->
    </div><!-- end .navbar-inner -->
</nav>

<!-- ======================================================
     SHARED JAVASCRIPT
     Placed here so it runs on every page that includes navbar.php
     ====================================================== -->
<script>
// -------------------------------------------------------
// THEME TOGGLE — Dark / Light Mode
// We save the user's choice in localStorage so it persists
// across pages and browser sessions.
// -------------------------------------------------------

// Apply the saved theme as soon as this script runs
// (before the page fully renders) to prevent a "flash" of wrong theme
(function applyTheme() {
    var saved = localStorage.getItem('cinelog_theme');
    if (saved === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
    }
})();

// Update the toggle button text to match the current theme
// We run this after the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    updateToggleButton();
    initHamburger();
    initScrollTopButton();
});

function updateToggleButton() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    var isDark = document.body.getAttribute('data-theme') === 'dark';
    btn.textContent = isDark ? '☀️ Light' : '🌙 Dark';
}

// Called when the toggle button is clicked
function toggleTheme() {
    var body = document.body;
    if (body.getAttribute('data-theme') === 'dark') {
        body.removeAttribute('data-theme');
        localStorage.setItem('cinelog_theme', 'light');
    } else {
        body.setAttribute('data-theme', 'dark');
        localStorage.setItem('cinelog_theme', 'dark');
    }
    updateToggleButton();
}

// -------------------------------------------------------
// HAMBURGER MENU — Mobile Navigation
// Toggles the .is-open class on the menu and the button
// -------------------------------------------------------
function initHamburger() {
    var hamburger = document.getElementById('nav-hamburger');
    var navLinks  = document.getElementById('navbar-links');

    if (!hamburger || !navLinks) return;

    hamburger.addEventListener('click', function() {
        var isOpen = navLinks.classList.contains('is-open');

        if (isOpen) {
            // Close the menu
            navLinks.classList.remove('is-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        } else {
            // Open the menu
            navLinks.classList.add('is-open');
            hamburger.classList.add('is-open');
            hamburger.setAttribute('aria-expanded', 'true');
        }
    });

    // Close the menu if user clicks anywhere outside the navbar
    document.addEventListener('click', function(event) {
        var navbar = document.querySelector('.navbar');
        if (navbar && !navbar.contains(event.target)) {
            navLinks.classList.remove('is-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });

    // Close the menu when a link inside it is clicked
    // (important on single-page-like navigations)
    navLinks.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            navLinks.classList.remove('is-open');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });
}

// -------------------------------------------------------
// SCROLL TO TOP BUTTON
// Shows a button in the bottom-right corner when user
// has scrolled down more than 300px
// -------------------------------------------------------
function initScrollTopButton() {
    var btn = document.getElementById('scroll-top-btn');
    if (!btn) return;

    // Listen for scroll events on the page
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    });

    btn.addEventListener('click', function() {
        // Smoothly scroll back to the top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}
</script>