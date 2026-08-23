<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'notifications';
$filter = $_GET['filter'] ?? 'all';
$validFilters = ['all', 'mention', 'like', 'retweet', 'follow'];
if (!in_array($filter, $validFilters, true)) {
    $filter = 'all';
}

$notificationQuery = ['userId' => $me['_id']];
if ($filter !== 'all') {
    $notificationQuery['type'] = $filter;
}

$notifications = iterator_to_array($db->notifications->find(
    $notificationQuery,
    ['sort' => ['created' => -1], 'limit' => 50]
));

// Mark all as read once viewed.
$db->notifications->updateMany(['userId' => $me['_id'], 'read' => false], ['$set' => ['read' => true]]);

$icons = ['like' => 'heart', 'follow' => 'user-round', 'reply' => 'message-circle', 'retweet' => 'repeat-2', 'message' => 'mail'];
$verbs = [
    'like' => 'liked your post',
    'follow' => 'followed you',
    'reply' => 'replied to your post',
    'retweet' => 'reposted your post',
    'message' => 'sent you a message',
];

$pageTitle = 'Notifications';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="notifications-header"><h2>Notifications</h2><p>Stay updated with what's happening</p></div>
        <nav class="notification-tabs" aria-label="Notification filters">
            <a class="<?php echo $filter === 'all' ? 'active' : ''; ?>" href="notifications.php">All</a>
            <a class="<?php echo $filter === 'mention' ? 'active' : ''; ?>" href="notifications.php?filter=mention">Mentions</a>
            <a class="<?php echo $filter === 'like' ? 'active' : ''; ?>" href="notifications.php?filter=like">Likes</a>
            <a class="<?php echo $filter === 'retweet' ? 'active' : ''; ?>" href="notifications.php?filter=retweet">Reposts</a>
            <a class="<?php echo $filter === 'follow' ? 'active' : ''; ?>" href="notifications.php?filter=follow">Follows</a>
        </nav>

        <?php if (empty($notifications)): ?>
            <div class="empty-state"><p>No notifications yet.</p></div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <?php
                    if ($n['type'] === 'follow') {
                        $link = 'profile.php?id=' . (string) $n['fromUserId'];
                    } elseif ($n['type'] === 'message') {
                        $link = 'conversation.php?id=' . (string) $n['fromUserId'];
                    } elseif (!empty($n['tweetId'])) {
                        $link = 'tweet.php?id=' . (string) $n['tweetId'];
                    } else {
                        $link = '#';
                    }
                ?>
                <?php $preview = !empty($n['tweetId']) ? $db->tweets->findOne(['_id' => $n['tweetId']], ['projection' => ['image' => 1, 'body' => 1]]) : null; ?>
                <a class="notif-item <?php echo $n['read'] ? '' : 'unread'; ?>" href="<?php echo h($link); ?>">
                    <span class="notif-icon notif-<?php echo h($n['type']); ?>"><i data-lucide="<?php echo h($icons[$n['type']] ?? 'bell'); ?>"></i></span>
                    <img class="avatar-sm" src="<?php echo h($n['fromAvatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                    <span class="notif-text">
                        <span><b><?php echo h($n['fromUsername']); ?></b> <?php echo h($verbs[$n['type']] ?? 'interacted with you'); ?> <span class="timestamp"> · <?php echo h(time_ago($n['created'])); ?></span></span>
                        <?php if ($preview && !empty($preview['body'])): ?><small><?php echo h($preview['body']); ?></small><?php endif; ?>
                    </span>
                    <?php if ($preview && !empty($preview['image'])): ?><img class="notif-preview" src="<?php echo h($preview['image']); ?>" alt="Post image"><?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <aside class="right-col notification-rail">
        <div class="widget search-box">
            <form action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form>
        </div>
        <section class="widget notification-prompt">
            <div class="notification-prompt-icon"><i data-lucide="bell"></i></div>
            <div><h3>Never miss a beat</h3><p>Turn on push notification to get real-time updates.</p></div>
            <button type="button" class="enable-notifications">Enable Notifications</button>
        </section>
    </aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
