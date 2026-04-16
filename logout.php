<?php
// ============================================================
// logout.php — CobraClimb Session Destroy
// CSC 4370/6370 · Spring 2026
// ============================================================
session_start();

// Expire the session cookie immediately
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Clear all session variables then destroy
session_unset();
session_destroy();

// Redirect to login with confirmation message
header('Location: login.php?msg=logged_out');
exit();
