<?php
// includes/header.php — Shared HTML header included on every page
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_user = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CobraClimb 🐍🪜</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <div class="logo">🐍 CobraClimb <span>🪜</span></div>
        <?php if ($current_user): ?>
        <nav class="main-nav">
            <span class="nav-user">👤 <?= $current_user ?></span>
            <a href="game.php" class="<?= $current_page==='game.php'?'active':'' ?>">🎲 Game</a>
            <a href="leaderboard.php" class="<?= $current_page==='leaderboard.php'?'active':'' ?>">🏆 Leaderboard</a>
            <a href="logout.php" class="nav-logout">🚪 Logout</a>
        </nav>
        <?php endif; ?>
    </div>
</header>
<main class="main-content">
