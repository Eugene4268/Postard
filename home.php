<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'home';
$contentPreferences = (array) ($me['contentPreferences'] ?? []);
$defaultFeed = in_array($contentPreferences['defaultFeed'] ?? '', ['recommended', 'following', 'popular'], true) ? $contentPreferences['defaultFeed'] : 'recommended';
$requestedFeed = $_GET['feed'] ?? '';
$feed = $requestedFeed === 'following' ? 'following' : ($requestedFeed === 'popular' ? 'popular' : ($requestedFeed === 'for-you' ? 'recommended' : $defaultFeed));

// Everyone the current user follows, plus themselves, defines the home feed.
$followingDocs = iterator_to_array($db->following->find(['follower' => $me['_id']]));
$followingIds = array_map(fn($d) => $d['followingId'], $followingDocs);
$feedAuthorIds = $followingIds;
$feedAuthorIds[] = $me['_id'];

$trendingCounts = [];
$recentTweets = $db->tweets->find([], ['projection' => ['body' => 1]]);
foreach ($recentTweets as $recentTweet) {
    preg_match_all('/(?<!\w)#([a-zA-Z0-9_]+)/', (string) ($recentTweet['body'] ?? ''), $matches);
    foreach ($matches[1] as $hashtag) {
        $key = strtolower($hashtag);
        $trendingCounts[$key] = ($trendingCounts[$key] ?? 0) + 1;
    }
}
arsort($trendingCounts);
$trending = array_slice($trendingCounts, 0, 3, true);

$communities = [];
$suggestedUsers = iterator_to_array($db->users->find(
    ['_id' => ['$nin' => $feedAuthorIds]],
    ['sort' => ['created' => -1, 'username' => 1], 'limit' => 3]
));

$tweets = $db->tweets->find(
    ['authorId' => ['$in' => $feed === 'following' ? $followingIds : $feedAuthorIds], 'replyTo' => ['$exists' => false]],
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
            <h2>Home</h2>
            <button class="header-action" type="button" aria-label="More options"><i data-lucide="more-horizontal"></i></button>
        </div>

        <form id="compose" class="composer composer-form" action="create_tweet.php" method="post" enctype="multipart/form-data" data-maxlen="<?php echo MAX_TWEET_LENGTH; ?>">
            <?php echo csrf_field(); ?>
            <img class="avatar" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
            <div class="composer-content">
                <span class="composer-kicker">Share something with your circle</span>
                <textarea name="body" placeholder="What's happening, <?php echo h($me['displayName'] ?? $me['username']); ?>?" maxlength="<?php echo MAX_TWEET_LENGTH; ?>" required></textarea>
                <img class="image-preview">
                <div class="composer-footer">
                    <div class="composer-tools"><label class="file-label" title="Add image"><i data-lucide="image"></i><input type="file" name="image" accept="image/*" style="display:none"></label><button type="button" aria-label="Add GIF"><i data-lucide="file-image"></i></button><button type="button" aria-label="Create poll"><i data-lucide="bar-chart-3"></i></button><button type="button" aria-label="Add emoji"><i data-lucide="smile"></i></button><button type="button" aria-label="Schedule"><i data-lucide="calendar-days"></i></button></div>
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

        <nav class="feed-tabs" aria-label="Feed filters"><a class="<?php echo $feed === 'recommended' ? 'active' : ''; ?>" href="home.php?feed=for-you">For you</a><a class="<?php echo $feed === 'following' ? 'active' : ''; ?>" href="home.php?feed=following">Following</a><a class="<?php echo $feed === 'popular' ? 'active' : ''; ?>" href="home.php?feed=popular">Popular</a></nav>

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
                <i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard">
            </form>
        </div>
        <section class="widget trend-widget">
            <h3><i data-lucide="trending-up"></i> Trending Now</h3>
            <?php if (empty($trending)): ?>
                <p class="widget-empty">There is nothing here yet.</p>
            <?php else: ?>
                <?php foreach ($trending as $hashtag => $count): ?>
                    <a href="search.php?q=%23<?php echo rawurlencode($hashtag); ?>"><b># <?php echo h($hashtag); ?></b><span><?php echo number_format($count); ?> <?php echo $count === 1 ? 'post' : 'posts'; ?></span></a>
                <?php endforeach; ?>
                <a class="widget-link" href="explore.php">Show more</a>
            <?php endif; ?>
        </section>
        <section class="widget people-widget">
            <div class="widget-title"><h3>Community</h3><a href="explore.php">View all</a></div>
            <?php if (empty($communities)): ?>
                <p class="widget-empty">There is nothing here yet.</p>
            <?php endif; ?>
        </section>
        <section class="widget people-widget suggested-widget">
            <div class="widget-title"><h3>Suggested for you</h3><a href="userlist.php">View all</a></div>
            <?php if (empty($suggestedUsers)): ?>
                <p class="widget-empty">There is nothing here yet.</p>
            <?php else: ?>
                <?php foreach ($suggestedUsers as $user): ?>
                    <div class="mini-person">
                        <a class="mini-person-profile" href="profile.php?id=<?php echo h((string) $user['_id']); ?>">
                            <img class="mini-avatar" src="<?php echo h($user['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                        </a>
                        <a class="mini-person-name" href="profile.php?id=<?php echo h((string) $user['_id']); ?>">
                            <b><?php echo h($user['displayName'] ?? $user['username']); ?></b>
                            <small>@<?php echo h($user['username']); ?></small>
                        </a>
                        <form method="post" action="follow.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo h((string) $user['_id']); ?>">
                            <input type="hidden" name="redirect" value="home.php">
                            <button type="submit">Follow</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
