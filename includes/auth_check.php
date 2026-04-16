<?php
// ============================================================
// includes/auth_check.php — Session Guard
// Redirects unauthenticated users to login.php
// Include at the TOP of every protected page
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Session timeout: 45 minutes of inactivity ──
define('SESSION_TIMEOUT', 45 * 60);

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Check last activity timestamp
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > SESSION_TIMEOUT) {
            // Session expired — destroy and redirect
            session_unset();
            session_destroy();
            header('Location: login.php?msg=session_expired');
            exit();
        }
    }
    // Refresh last activity timestamp on every valid request
    $_SESSION['last_activity'] = time();
} else {
    // Not logged in — redirect to login
    header('Location: login.php?msg=session_expired');
    exit();
}
