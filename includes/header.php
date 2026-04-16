<?php
// includes/header.php — Shared HTML header included on every page
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$current_user = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : '';
$current_page = basename($_SERVER['PHP_SELF']);

// Dynamic page title per file
$titles = [
    'login.php'       => 'Sign In',
    'register.php'    => 'Register',
    'game.php'        => 'Play Board',
    'leaderboard.php' => 'Leaderboard',
    'about.php'       => 'About Team',
    'logout.php'      => 'Logging Out',
];
$page_title = $titles[$current_page] ?? 'CobraClimb';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CobraClimb — PHP-driven Snakes and Ladders board game">
    <title>🐍 CobraClimb — <?= $page_title ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <div class="logo">🐍 CobraClimb <span>🪜</span></div>
        <?php if ($current_user): ?>
        <nav class="main-nav" aria-label="Main navigation">
            <span class="nav-user">👤 <?= $current_user ?></span>
            <a href="game.php"        class="<?= $current_page === 'game.php'        ? 'active' : '' ?>">🎲 Game</a>
            <a href="leaderboard.php" class="<?= $current_page === 'leaderboard.php' ? 'active' : '' ?>">🏆 Scores</a>
            <a href="about.php"       class="<?= $current_page === 'about.php'       ? 'active' : '' ?>">👥 Team</a>
            <a href="logout.php" class="nav-logout">🚪 Logout</a>
        </nav>
        <?php endif; ?>
    </div>
</header>
<main class="main-content">
