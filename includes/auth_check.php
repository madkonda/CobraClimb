<?php
// includes/auth_check.php — Session guard: redirect to login if not authenticated
// Include this at the TOP of every protected page (game.php, roll.php, leaderboard.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php?msg=session_expired');
    exit();
}
?>
