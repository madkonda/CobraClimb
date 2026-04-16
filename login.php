<?php
// ============================================================
// login.php — CobraClimb User Login
// CSC 4370/6370 · Spring 2026
// ============================================================
session_start();
require 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: game.php');
    exit();
}

$error        = '';
$username_val = '';

// ── Handle POST submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = trim($_POST['password'] ?? '');
    $username_val = htmlspecialchars($username);

    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required.';
    } else {
        $user = findUser($username);

        if ($user === null || !password_verify($password, $user['password'])) {
            // Intentionally vague — don't reveal which field is wrong
            $error = 'Invalid username or password. Please try again.';
        } else {
            // ── Successful login: start session ──
            session_regenerate_id(true);   // Prevent session fixation
            $_SESSION['user']       = $user['username'];
            $_SESSION['logged_in']  = true;
            $_SESSION['login_time'] = time();

            header('Location: game.php');
            exit();
        }
    }
}

// Check for messages passed via GET (e.g., session expired)
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

require 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">

        <h1>🐍 CobraClimb</h1>
        <h2>Sign in to play</h2>

        <?php if ($msg === 'session_expired'): ?>
            <div class="alert alert-warning">⏱ Your session expired. Please log in again.</div>
        <?php elseif ($msg === 'registered'): ?>
            <div class="alert alert-success">✅ Account created! You can now log in.</div>
        <?php elseif ($msg === 'logged_out'): ?>
            <div class="alert alert-info">👋 You have been logged out.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= $username_val ?>"
                    placeholder="Enter your username"
                    maxlength="20"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">🎲 Sign In &amp; Play</button>
        </form>

        <p class="auth-switch">
            New player? <a href="register.php">Create an account →</a>
        </p>

    </div>
</div>

<?php require 'includes/footer.php'; ?>
