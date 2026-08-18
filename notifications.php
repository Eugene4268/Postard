<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'notifications';

$notifications = iterator_to_array($db->notifications->find(
    ['userId' => $me['_id']],
    ['sort' => ['created' => -1], 'limit' => 50]
));

// Mark all as read once viewed.
$db->notifications->updateMany(['userId' => $me['_id'], 'read' => false], ['$set' => ['read' => true]]);

$icons = ['like' => '❤️', 'follow' => '👤', 'reply' => '💬', 'retweet' => '🔁', 'message' => '✉️'];
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
        <div class="main-header"><h2>Notifications</h2></div>

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
                <a class="notif-item <?php echo $n['read'] ? '' : 'unread'; ?>" href="<?php echo h($link); ?>">
                    <span class="notif-icon"><?php echo $icons[$n['type']] ?? '🔔'; ?></span>
                    <img class="avatar-sm" src="<?php echo h($n['fromAvatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                    <span class="notif-text">
                        <b>@<?php echo h($n['fromUsername']); ?></b> <?php echo h($verbs[$n['type']] ?? 'interacted with you'); ?>
                        <span class="timestamp"> · <?php echo h(time_ago($n['created'])); ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <aside class="right-col"></aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
