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
            $db->users->insertOne([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'displayName' => $username,
                'bio' => '',
                'avatar' => null,
                'banner' => null,
                'created' => new MongoDB\BSON\UTCDateTime(),
            ]);
            redirect('index.php?registered=1');
        }
    }
}
?>
<?php $pageTitle = 'Sign up'; include __DIR__ . '/partials/head.php'; ?>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="logo">🐦</div>
        <h1>Create your account</h1>
        <?php if ($error): ?><div class="error-msg"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post" action="register.php">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus value="<?php echo h($_POST['username'] ?? ''); ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo h($_POST['email'] ?? ''); ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            <div class="field">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn">Sign up</button>
        </form>
        <div class="auth-switch">Already have an account? <a href="index.php">Log in</a></div>
    </div>
</div>
</body>
</html>
