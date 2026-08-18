<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'home';

// Everyone the current user follows, plus themselves, defines the home feed.
$followingDocs = iterator_to_array($db->following->find(['follower' => $me['_id']]));
$followingIds = array_map(fn($d) => $d['followingId'], $followingDocs);
$followingIds[] = $me['_id'];

$tweets = $db->tweets->find(
    ['authorId' => ['$in' => $followingIds]],
    ['sort' => ['created' => -1], 'limit' => 50]
);
$tweets = iterator_to_array($tweets);

$pageTitle = 'Home';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header">
            <div>
                <span class="eyebrow">Your space</span>
                <h2>Home</h2>
            </div>
            <span class="live-indicator"><i></i> Live</span>
        </div>

        <form class="composer composer-form" action="create_tweet.php" method="post" enctype="multipart/form-data" data-maxlen="<?php echo MAX_TWEET_LENGTH; ?>">
            <?php echo csrf_field(); ?>
            <img class="avatar" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
            <div class="composer-content">
                <span class="composer-kicker">Share something with your circle</span>
                <textarea name="body" placeholder="What's happening?" maxlength="<?php echo MAX_TWEET_LENGTH; ?>" required></textarea>
                <img class="image-preview">
                <div class="composer-footer">
                    <label class="file-label" title="Add image">
                        🖼️ <input type="file" name="image" accept="image/*" style="display:none">
                    </label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span class="char-count">0 / <?php echo MAX_TWEET_LENGTH; ?></span>
                        <button type="submit" class="btn" disabled>Post</button>
                    </div>
                </div>
            </div>
        </form>

        <?php if (empty($tweets)): ?>
            <div class="empty-state home-empty-state">
                <div class="empty-orbit"><span>✦</span></div>
                <h3>Make your feed yours</h3>
                <p>Follow people whose updates you want to see, then come back for a timeline made just for you.</p>
                <a class="empty-cta" href="userlist.php">Discover people <span>→</span></a>
            </div>
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
        <div class="widget discover-widget">
            <span class="widget-label">Community</span>
            <h3>Find your people</h3>
            <p>Explore the community and build a feed that feels like home.</p>
            <a href="userlist.php" class="widget-link">Browse everyone <span>→</span></a>
        </div>
    </aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
