<?php
require_once __DIR__ . '/config.php';

if (current_user()) {
    redirect('home.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    rate_limit('login', 10, 60); // max 10 attempts per minute per session

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $db->users->findOne(['username' => $username]);

    if ($user && password_verify($password, $user['password'])) {
        log_in_user($user['_id']);
        redirect('home.php');
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<?php $pageTitle = 'Login'; include __DIR__ . '/partials/head.php'; ?>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="logo">🐦</div>
        <h1>Log in to Postard</h1>
        <?php if ($error): ?><div class="error-msg"><?php echo h($error); ?></div><?php endif; ?>
        <?php if (isset($_GET['registered'])): ?><div class="success-msg">Account created! Please log in.</div><?php endif; ?>
        <form method="post" action="index.php">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Log in</button>
        </form>
        <div class="auth-switch">No account? <a href="register.php">Sign up</a></div>
    </div>
</div>
</body>
</html>
