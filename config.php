<?php
// ============================================================
// config.php - Central configuration file
// Change these values to match your own setup
// ============================================================

// --- Database Settings ---
define('DB_HOST', 'localhost');   // Usually localhost for XAMPP
define('DB_USER', 'root');        // Default XAMPP MySQL username
define('DB_PASS', '');            // Default XAMPP MySQL password (empty)
define('DB_NAME', 'cinelog');     // The database name we created

// --- Website Settings ---
define('SITE_URL', 'http://localhost/cinelog'); // Base URL of your project
define('SITE_NAME', 'CineLog');

// --- Email Settings (Gmail) ---
// Use your Gmail address and your App Password (NOT your real password)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'omiomahim007@gmail.com'); // <-- Change this
define('MAIL_PASSWORD', 'eanr togk hufu leso'); // <-- Change this
define('MAIL_PORT', 587);
define('MAIL_FROM_NAME', 'CineLog App');