<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'explore';

$followingIds = array_map(
    fn($d) => $d['followingId'],
    iterator_to_array($db->following->find(['follower' => $me['_id']]))
);
$excludedUserIds = $followingIds;
$excludedUserIds[] = $me['_id'];

$topicCounts = [];
$topicTweets = $db->tweets->find(
    ['retweetOf' => ['$exists' => false]],
    ['projection' => ['body' => 1]]
);
foreach ($topicTweets as $topicTweet) {
    preg_match_all('/(?<!\w)#([a-zA-Z0-9_]+)/', (string) ($topicTweet['body'] ?? ''), $matches);
    foreach ($matches[1] as $topic) {
        $key = strtolower($topic);
        $topicCounts[$key] = ($topicCounts[$key] ?? 0) + 1;
    }
}
arsort($topicCounts);
$topics = array_slice($topicCounts, 0, 6, true);

$communities = [];
$events = [];
$suggestedUsers = iterator_to_array($db->users->find(
    ['_id' => ['$nin' => $excludedUserIds]],
    ['sort' => ['created' => -1, 'username' => 1], 'limit' => 3]
));

$pageTitle = 'Explore';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="explore-header"><h2>Explore</h2><p>Discover what's happening around the world.</p></div>
        <nav class="explore-tabs" aria-label="Explore filters"><a class="active" href="explore.php">For you</a><a href="explore.php?tab=trending">Trending</a><a href="explore.php?tab=news">News</a><a href="explore.php?tab=sports">Sports</a><a href="explore.php?tab=entertainment">Entertainment</a></nav>

        <section class="explore-section">
            <div class="explore-section-heading"><h3>Trending Topics</h3></div>
            <?php if (empty($topics)): ?>
                <div class="explore-empty">There is nothing here yet.</div>
            <?php else: ?>
                <div class="topic-grid">
                    <?php $topicRank = 1; foreach ($topics as $topic => $count): ?>
                        <a class="topic-card" href="search.php?q=%23<?php echo rawurlencode($topic); ?>">
                            <span class="topic-rank"><?php echo $topicRank++; ?> · Trending</span>
                            <strong># <?php echo h($topic); ?></strong>
                            <small><?php echo number_format($count); ?> <?php echo $count === 1 ? 'post' : 'posts'; ?></small>
                            <i data-lucide="trending-up"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a class="explore-more" href="search.php">Show more</a>
            <?php endif; ?>
        </section>

        <section class="explore-section community-section">
            <div class="explore-section-heading"><h3>Popular Communities</h3><a href="userlist.php">View all</a></div>
            <?php if (empty($communities)): ?>
                <div class="explore-empty">There is nothing here yet.</div>
            <?php endif; ?>
        </section>
    </main>

    <aside class="right-col">
        <div class="widget search-box">
            <form action="search.php" method="get">
                <input type="text" name="q" placeholder="Search Postard">
            </form>
        </div>
        <section class="widget events-widget">
            <div class="widget-title"><h3>Upcoming Events</h3><a href="explore.php">View all</a></div>
            <?php if (empty($events)): ?><p class="widget-empty">There is nothing here yet.</p><?php endif; ?>
        </section>
        <section class="widget people-widget suggested-widget">
            <div class="widget-title"><h3>Suggested for you</h3><a href="userlist.php">View all</a></div>
            <?php if (empty($suggestedUsers)): ?>
                <p class="widget-empty">There is nothing here yet.</p>
            <?php else: ?>
                <?php foreach ($suggestedUsers as $user): ?>
                    <div class="mini-person">
                        <a class="mini-person-profile" href="profile.php?id=<?php echo h((string) $user['_id']); ?>"><img class="mini-avatar" src="<?php echo h($user['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""></a>
                        <a class="mini-person-name" href="profile.php?id=<?php echo h((string) $user['_id']); ?>"><b><?php echo h($user['displayName'] ?? $user['username']); ?></b><small>@<?php echo h($user['username']); ?></small></a>
                        <form method="post" action="follow.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo h((string) $user['_id']); ?>">
                            <input type="hidden" name="redirect" value="explore.php">
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
