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
<div class="auth-layout">
    <section class="auth-showcase">
        <img class="auth-showcase-image" src="assets/img/login_page.png" alt="" aria-hidden="true">
        <div class="auth-showcase-content">
            <a class="auth-brand" href="index.php"><img src="assets/img/postard_logo.png" alt="Postard"></a>
            <div class="auth-copy">
                <h1>Connect.<br>Share.<br><span>Grow</span> together.</h1>
                <p>Postard is your space to share ideas,<br>connect with communities and<br>build your network.</p>
            </div>
            <div class="auth-orbit" aria-hidden="true"></div>
            <div class="auth-benefits">
                <div><strong class="benefit-icon people-icon">◌</strong><span>Connect with<br>amazing people</span></div>
                <div><strong class="benefit-icon spark-icon">ϟ</strong><span>Stay updated with<br>what matters</span></div>
                <div><strong class="benefit-icon globe-icon">◎</strong><span>Explore topics<br>you love</span></div>
            </div>
        </div>
    </section>
    <main class="auth-panel">
        <div class="auth-card">
        <div class="auth-heading">
            <h2>Welcome back!</h2>
            <p>Log in to continue to postard</p>
        </div>
        <?php if ($error): ?><div class="error-msg"><?php echo h($error); ?></div><?php endif; ?>
        <?php if (isset($_GET['registered'])): ?><div class="success-msg">Account created! Please log in.</div><?php endif; ?>
        <form method="post" action="index.php">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="username">Email or Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your email or username" required autofocus autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="password-input"><input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password"><button type="button" class="password-toggle" aria-label="Show password">◉</button></div>
            </div>
            <button type="submit" class="btn">Log in</button>
        </form>
        <a class="forgot-link" href="#">Forgot password?</a>
        <div class="auth-divider"><span>Or continue with</span></div>
        <div class="social-login"><button type="button"><b class="google-mark">G</b>Continue with Google</button><button type="button"><b class="apple-mark">●</b>Continue with Apple</button></div>
        <div class="auth-switch">Don't have an account? <a href="register.php">Signup</a></div>
        </div>
    </main>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
