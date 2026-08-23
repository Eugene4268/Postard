<?php
require_once __DIR__ . '/config.php';

if (current_user()) {
    redirect('home.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    rate_limit('register', 5, 60);

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!valid_username($username)) {
        $error = 'Username must be 3-20 characters: letters, numbers, underscore only.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!valid_password($password)) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $existing = $db->users->findOne([
            '$or' => [['username' => $username], ['email' => $email]],
        ]);
        if ($existing) {
            $error = 'That username or email is already taken.';
        } else {
            $result = $db->users->insertOne([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'displayName' => $username,
                'bio' => '',
                'avatar' => null,
                'banner' => null,
                'created' => new MongoDB\BSON\UTCDateTime(),
            ]);
            log_in_user($result->getInsertedId());
            redirect('home.php');
        }
    }
}
?>
<?php $pageTitle = 'Sign up'; include __DIR__ . '/partials/head.php'; ?>
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
            <h2>Create your account</h2>
            <p>Join postard and start connecting</p>
        </div>
        <?php if ($error): ?><div class="error-msg"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="register.php">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required autofocus autocomplete="username" value="<?php echo h($_POST['username'] ?? ''); ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required autocomplete="email" value="<?php echo h($_POST['email'] ?? ''); ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="password-input"><input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password"><button type="button" class="password-toggle" aria-label="Show password"></button></div>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-input"><input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password"><button type="button" class="password-toggle" aria-label="Show password"></button></div>
            </div>
            <button type="submit" class="btn">Sign up</button>
        </form>
        <div class="auth-switch">Already have an account? <a href="index.php">Log in</a></div>
        </div>
    </main>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
