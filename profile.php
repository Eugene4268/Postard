<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'profile';

$profileId = to_object_id($_GET['id'] ?? '');
if (!$profileId) {
    redirect('home.php');
}

$profile = $db->users->findOne(['_id' => $profileId]);
if (!$profile) {
    http_response_code(404);
    $pageTitle = 'Not found';
    include __DIR__ . '/partials/head.php';
    echo '<div class="app">';
    include __DIR__ . '/partials/navbar.php';
    echo '<main class="main"><div class="empty-state"><p>User not found.</p></div></main></div></body></html>';
    exit;
}

$isMe = (string) $profile['_id'] === (string) $me['_id'];
$isFollowing = !$isMe && $db->following->findOne(['follower' => $me['_id'], 'followingId' => $profile['_id']]) !== null;

$followerCount = $db->following->countDocuments(['followingId' => $profile['_id']]);
$followingCount = $db->following->countDocuments(['follower' => $profile['_id']]);

$tweets = iterator_to_array($db->tweets->find(
    ['authorId' => $profile['_id']],
    ['sort' => ['created' => -1], 'limit' => 50]
));

$pageTitle = $profile['displayName'] ?? $profile['username'];
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header"><h2><?php echo h($profile['displayName'] ?? $profile['username']); ?></h2></div>

        <img class="profile-banner" src="<?php echo h($profile['banner'] ?? 'assets/img/default-banner.svg'); ?>" alt="">
        <div class="profile-info">
            <div class="profile-avatar-wrap">
                <img class="profile-avatar" src="<?php echo h($profile['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
            </div>

            <?php if ($isMe): ?>
                <a href="edit_profile.php" class="btn secondary" style="width:auto; float:right; margin-top:12px;">Edit profile</a>
            <?php else: ?>
                <div style="float:right; margin-top:12px; display:flex; gap:8px;">
                    <a href="conversation.php?id=<?php echo h((string) $profile['_id']); ?>" class="btn secondary" style="width:auto;">Message</a>
                    <form method="post" action="follow.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo h((string) $profile['_id']); ?>">
                        <input type="hidden" name="redirect" value="profile.php?id=<?php echo h((string) $profile['_id']); ?>">
                        <button type="submit" class="follow-btn <?php echo $isFollowing ? 'following' : 'not-following'; ?>">
                            <?php echo $isFollowing ? 'Following' : 'Follow'; ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <p class="profile-name"><?php echo h($profile['displayName'] ?? $profile['username']); ?></p>
            <p class="profile-handle">@<?php echo h($profile['username']); ?></p>
            <?php if (!empty($profile['bio'])): ?><p class="profile-bio"><?php echo nl2br(h($profile['bio'])); ?></p><?php endif; ?>

            <div class="profile-stats">
                <span><b><?php echo (int) $followingCount; ?></b> Following</span>
                <span><b><?php echo (int) $followerCount; ?></b> Followers</span>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active">Posts</div>
        </div>

        <?php if (empty($tweets)): ?>
            <div class="empty-state"><p>No posts yet.</p></div>
        <?php else: ?>
            <?php foreach ($tweets as $tweet): include __DIR__ . '/partials/tweet_card.php'; endforeach; ?>
        <?php endif; ?>
    </main>

    <aside class="right-col">
        <div class="widget search-box">
            <form action="search.php" method="get">
                <input type="text" name="q" placeholder="Search Postard">
            </form>
        </div>
    </aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
