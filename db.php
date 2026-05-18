<?php
// ============================================================
// db.php - Database Connection
// We use mysqli (MySQL Improved) to connect to our database
// ============================================================

// Pull in our settings from config.php
require_once 'config.php';

// mysqli_connect() tries to open a connection to MySQL
// It takes: host, username, password, database name
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if the connection failed
// mysqli_connect_error() returns the error message if something went wrong
if (!$conn) {
    // die() stops the script and shows an error message
    die("Database connection failed: " . mysqli_connect_error());
}

// If we reach this point, the connection worked!
// The $conn variable is now available to use in any file that includes db.php