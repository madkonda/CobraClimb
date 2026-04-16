<?php
// ============================================================
// logout.php — CobraClimb Session Destroy
// CSC 4370/6370 · Spring 2026
// ============================================================
session_start();

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login with logged-out message
header('Location: login.php?msg=logged_out');
exit();
