<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'settings';
$error = null;
$success = null;

if (!empty($_GET['verify'])) {
    $verifiedUser = $db->users->findOne(['_id' => $me['_id'], 'emailVerificationToken' => hash('sha256', (string) $_GET['verify']), 'emailVerificationExpires' => ['$gt' => new MongoDB\BSON\UTCDateTime()]]);
    if ($verifiedUser) {
        $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['emailVerified' => true], '$unset' => ['emailVerificationToken' => '', 'emailVerificationExpires' => '']]);
        $me['emailVerified'] = true;
        $success = 'Email address verified.';
    } else {
        $error = 'That verification link is invalid or expired.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'resend_verification') {
        $token = bin2hex(random_bytes(32));
        $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['emailVerificationToken' => hash('sha256', $token), 'emailVerificationExpires' => new MongoDB\BSON\UTCDateTime((time() + 86400) * 1000)]]);
        $verificationUrl = rtrim(APP_URL, '/') . '/account.php?verify=' . urlencode($token);
        $success = mail($me['email'], 'Verify your Postard email', "Verify your email address: $verificationUrl") ? 'A verification email has been sent.' : 'Verification link created, but email delivery is unavailable in this environment.';
    } elseif ($action === 'contact') {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($phone !== '' && !preg_match('/^[0-9+() .-]{7,25}$/', $phone)) {
            $error = 'Please enter a valid phone number.';
        } else {
            $existing = $db->users->findOne(['email' => $email, '_id' => ['$ne' => $me['_id']]]);
            if ($existing) {
                $error = 'That email address is already in use.';
            } else {
                $update = ['email' => $email, 'phone' => $phone];
                if ($email !== ($me['email'] ?? '')) $update['emailVerified'] = false;
                $db->users->updateOne(['_id' => $me['_id']], ['$set' => $update]);
                $success = 'Contact details updated.';
                $me = current_user();
            }
        }
    } elseif ($action === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (!password_verify($currentPassword, $me['password'] ?? '')) {
            $error = 'Your current password is incorrect.';
        } elseif (!valid_password($newPassword)) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } else {
            $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['password' => password_hash($newPassword, PASSWORD_BCRYPT)]]);
            $success = 'Password changed successfully.';
        }
    } elseif ($action === 'two_factor') {
        $enabled = !empty($_POST['enabled']);
        $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['twoFactorEnabled' => $enabled]]);
        $success = $enabled ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.';
        $me['twoFactorEnabled'] = $enabled;
    } elseif ($action === 'logout_all') {
        redirect('logout.php');
    }
}

$pageTitle = 'Account';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <main class="main account-main">
        <header class="settings-header"><h2>Account</h2><p>Manage your account details and security</p></header>
        <?php if ($error): ?><div class="error-msg account-message"><?php echo h($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success-msg account-message"><?php echo h($success); ?></div><?php endif; ?>

        <section class="account-section">
            <div class="account-section-heading"><h3>Profile Information</h3><p>Manage your personal account details</p></div>
            <div class="account-profile"><img class="profile-avatar account-avatar" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""><div><strong><?php echo h($me['displayName'] ?? $me['username']); ?></strong><span>@<?php echo h($me['username']); ?></span><span><?php echo h($me['email'] ?? ''); ?></span><?php if (!empty($me['phone'])): ?><span><?php echo h($me['phone']); ?></span><?php endif; ?></div></div>
            <a class="account-button secondary" href="edit_profile.php">Edit Profile</a>
        </section>

        <section class="account-section"><div class="account-section-heading"><h3>Login &amp; Security</h3><p>Keep your account secure</p></div>
            <button class="account-option" type="button" data-password-toggle><span class="settings-icon"><i data-lucide="lock"></i></span><span><strong>Change Password</strong><small>Update your account password</small></span><i class="settings-arrow" data-lucide="chevron-right"></i></button>
            <form class="account-password-form" method="post" hidden><?php echo csrf_field(); ?><input type="hidden" name="action" value="password"><div class="field"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div><div class="field"><label>New password</label><input type="password" name="new_password" required autocomplete="new-password"></div><div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" required autocomplete="new-password"></div><button class="account-button" type="submit">Update Password</button></form>
            <form class="account-option account-option-form" method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="two_factor"><input type="hidden" name="enabled" value="<?php echo !empty($me['twoFactorEnabled']) ? '0' : '1'; ?>"><span class="settings-icon"><i data-lucide="shield-check"></i></span><span><strong>Two-Factor Authentication</strong><small>Add an extra layer of security - <?php echo !empty($me['twoFactorEnabled']) ? 'On' : 'Off'; ?></small></span><button class="account-status" type="submit"><?php echo !empty($me['twoFactorEnabled']) ? 'Disable' : 'Enable'; ?></button></form>
            <div class="account-option"><span class="settings-icon"><i data-lucide="monitor"></i></span><span><strong>Active Sessions</strong><small>Current browser session</small></span><span class="account-current">This device</span></div>
            <form class="account-option-form" method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="logout_all"><button class="account-danger-link" type="submit">Sign out of all devices</button></form>
        </section>

        <section class="account-section"><div class="account-section-heading"><h3>Email Address</h3><p><?php echo h($me['email'] ?? ''); ?> <?php echo !empty($me['emailVerified']) ? '✓ Verified' : 'Not verified'; ?></p></div><form class="account-contact-form" method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="contact"><div class="account-fields"><div class="field"><label for="account-email">Email address</label><input id="account-email" type="email" name="email" value="<?php echo h($me['email'] ?? ''); ?>" required></div><div class="field"><label for="account-phone">Phone number (optional)</label><input id="account-phone" type="tel" name="phone" value="<?php echo h($me['phone'] ?? ''); ?>"></div></div><button class="account-button" type="submit">Save Contact Details</button></form></section>

        <?php if (empty($me['emailVerified'])): ?><section class="account-section"><div class="account-section-heading"><h3>Email Verification</h3><p>Verify your email address to help secure your account.</p></div><form method="post" class="verification-form"><?php echo csrf_field(); ?><input type="hidden" name="action" value="resend_verification"><button class="account-button secondary" type="submit">Resend verification email</button></form></section><?php endif; ?>

        <section class="account-section"><div class="account-section-heading"><h3>Account Preferences</h3><p>Language - English &nbsp; | &nbsp; Time zone - IST (UTC+5:30)</p></div><button class="account-option" type="button" data-language-choice><span class="settings-icon"><i data-lucide="globe-2"></i></span><span><strong>Language &amp; Time Zone</strong><small>English - IST (UTC+5:30)</small></span><i class="settings-arrow" data-lucide="chevron-right"></i></button></section>

        <section class="account-section danger-zone"><div class="account-section-heading"><h3>Danger Zone</h3><p>Actions here can permanently affect your account.</p></div><div class="danger-action"><div><strong>Deactivate Account</strong><small>Temporarily disable your Postard account.</small></div><button class="account-button secondary" type="button" disabled>Deactivate</button></div><div class="danger-action"><div><strong>Delete Account</strong><small>Permanently delete your account and associated data.</small></div><button class="account-button danger" type="button" data-delete-account>Delete Account</button></div></section>
    </main>
    <aside class="right-col"><div class="widget search-box"><form action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form></div></aside>
</div>
<div class="account-modal" hidden><div class="account-modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-title"><h2 id="delete-title">Delete your account?</h2><p>This action cannot be undone. All your posts, messages, and account data will be permanently deleted.</p><div><button type="button" class="account-button secondary" data-delete-cancel>Cancel</button><form method="post" action="delete_account.php" class="delete-form"><?php echo csrf_field(); ?><button class="account-button danger" type="submit">Delete Account</button></form></div></div></div>
<script src="assets/js/app.js"></script>
</body>
</html>
