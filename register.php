<?php
// register.php — New user registration with flat-file storage
session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: game.php');
    exit();
}

$error   = '';
$success = '';
$username_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize + validate input
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $username_val = htmlspecialchars($username);

    if (empty($username) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'Username must be between 3 and 20 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Load existing users from flat file
        $data_file = 'data/users.json';
        $users = [];
        if (file_exists($data_file)) {
            $users = json_decode(file_get_contents($data_file), true) ?? [];
        }

        // Check for duplicate username
        foreach ($users as $u) {
            if (strtolower($u['username']) === strtolower($username)) {
                $error = 'Username already taken. Please choose another.';
                break;
            }
        }

        if (empty($error)) {
            // Save new user with hashed password
            $users[] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created'  => date('Y-m-d H:i:s'),
            ];
            file_put_contents($data_file, json_encode($users, JSON_PRETTY_PRINT));
            $success = 'Account created! You can now log in.';
            $username_val = '';
        }
    }
}

require 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>🐍 CobraClimb</h1>
        <h2>Create Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?> <a href="login.php">Log in →</a></div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?= $username_val ?>"
                       placeholder="3–20 chars, letters/numbers only"
                       maxlength="20"
                       pattern="[a-zA-Z0-9_]+"
                       title="Letters, numbers, and underscores only" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Min 6 characters" required>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm"
                       placeholder="Re-enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary">🎲 Register</button>
        </form>

        <p class="auth-switch">Already have an account? <a href="login.php">Log in here</a></p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
