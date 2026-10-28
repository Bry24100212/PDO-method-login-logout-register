<?php
// auth.php - Session Protection Header

// 1. Start or resume the existing session
session_start();

// 2. Check if the user is NOT logged in (user_id session variable is missing)
if (!isset($_SESSION['user_id'])) {
    // Redirect unauthenticated user to the login page
    header('Location: login.php');
    exit(); // Always call exit() after a header redirect
}
?>
