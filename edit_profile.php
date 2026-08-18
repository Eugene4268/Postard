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

    if ($displayName === '' || mb_strlen($displayName) > 50) {
        $error = 'Display name must be 1-50 characters.';
    } elseif (mb_strlen($bio) > 160) {
        $error = 'Bio must be 160 characters or fewer.';
    } else {
        $update = [
            'displayName' => $displayName,
            'bio' => $bio,
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
<?php $pageTitle = 'Edit profile'; include __DIR__ . '/partials/head.php'; ?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <main class="main">
        <div class="main-header"><h2>Edit profile</h2></div>
        <div style="padding:16px; max-width:500px;">
            <?php if ($error): ?><div class="error-msg"><?php echo h($error); ?></div><?php endif; ?>
            <form method="post" action="edit_profile.php" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label for="display_name">Display name</label>
                    <input type="text" id="display_name" name="display_name" maxlength="50" required
                           value="<?php echo h($me['displayName'] ?? $me['username']); ?>">
                </div>
                <div class="field">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" maxlength="160" rows="3"><?php echo h($me['bio'] ?? ''); ?></textarea>
                </div>
                <div class="field">
                    <label for="avatar">Profile photo</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                </div>
                <div class="field">
                    <label for="banner">Banner image</label>
                    <input type="file" id="banner" name="banner" accept="image/*">
                </div>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
