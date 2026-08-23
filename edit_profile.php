<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'profile';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $displayName = trim($_POST['display_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $website = trim($_POST['website'] ?? '');

    if ($displayName === '' || mb_strlen($displayName) > 50) {
        $error = 'Display name must be 1-50 characters.';
    } elseif (mb_strlen($bio) > 160) {
        $error = 'Bio must be 160 characters or fewer.';
    } elseif (mb_strlen($location) > 80) {
        $error = 'Location must be 80 characters or fewer.';
    } elseif ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = 'Website must be a valid URL.';
    } else {
        $update = [
            'displayName' => $displayName,
            'bio' => $bio,
            'location' => $location,
            'website' => $website,
        ];

        $avatarPath = handle_image_upload('avatar', 'avatars');
        if ($avatarPath) {
            $update['avatar'] = $avatarPath;
        }
        $bannerPath = handle_image_upload('banner', 'banners');
        if ($bannerPath) {
            $update['banner'] = $bannerPath;
        }

        $db->users->updateOne(['_id' => $me['_id']], ['$set' => $update]);
        redirect('profile.php?id=' . (string) $me['_id']);
    }
}
?>
<?php $pageTitle = 'Edit Profile'; include __DIR__ . '/partials/head.php'; ?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <main class="main">
        <header class="edit-profile-header"><h2>Edit Profile</h2></header>
        <div class="edit-profile-layout">
            <section class="edit-profile-card">
            <?php if ($error): ?><div class="error-msg"><?php echo h($error); ?></div><?php endif; ?>
            <form method="post" action="edit_profile.php" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="edit-photo-heading"><h3>Profile photo</h3><p>This will be displayed on your profile</p><label class="edit-avatar-picker"><img class="profile-avatar" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""><span>+</span><input type="file" id="avatar" name="avatar" accept="image/*"></label></div>
                <div class="edit-fields-grid">
                <div class="field">
                    <label for="display_name">Name</label>
                    <input type="text" id="display_name" name="display_name" maxlength="50" required
                           value="<?php echo h($me['displayName'] ?? $me['username']); ?>">
                </div>
                <div class="field"><label for="username">Username</label><input type="text" id="username" value="@<?php echo h($me['username']); ?>" disabled><small>Your unique Postard username.</small></div>
                <div class="field">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" maxlength="160" rows="3"><?php echo h($me['bio'] ?? ''); ?></textarea>
                </div>
                <div class="field">
                    <label for="banner">Banner image</label>
                    <input type="file" id="banner" name="banner" accept="image/*">
                </div>
                <div class="field">
                    <label for="email">Email</label><input type="email" id="email" value="<?php echo h($me['email'] ?? ''); ?>" disabled>
                </div>
                <div class="field"><label for="location">Location</label><input type="text" id="location" name="location" maxlength="80" value="<?php echo h($me['location'] ?? ''); ?>"></div>
                <div class="field edit-field-wide"><label for="website">Website</label><input type="url" id="website" name="website" placeholder="https://example.com" value="<?php echo h($me['website'] ?? ''); ?>"></div>
                </div>
                <div class="edit-profile-actions"><a class="account-button secondary" href="profile.php?id=<?php echo h((string) $me['_id']); ?>">Cancel</a><button type="submit" class="account-button">Save changes</button></div>
            </form>
            </section>
            <aside class="profile-preview-card"><h3>Profile preview</h3><p>This is how your profile will appear to others.</p><div class="preview-banner"><img src="<?php echo h($me['banner'] ?? 'assets/img/default-banner.svg'); ?>" alt=""></div><img class="preview-avatar" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""><strong><?php echo h($me['displayName'] ?? $me['username']); ?></strong><span>@<?php echo h($me['username']); ?></span><p><?php echo nl2br(h($me['bio'] ?? '')); ?></p><small><?php echo h($me['location'] ?? ''); ?> <?php echo h($me['website'] ?? ''); ?></small></aside>
        </div>
    </main>
</div>
</body>
</html>
