<?php
// index.php — Entry point: redirect to login or game
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: game.php');
} else {
    header('Location: login.php');
}
exit();
?>
