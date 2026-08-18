<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'messages';

$msgs = iterator_to_array($db->messages->find(
    ['$or' => [['senderId' => $me['_id']], ['recipientId' => $me['_id']]]],
    ['sort' => ['created' => -1]]
));

// Reduce to one row per conversation partner, keeping only the most recent message.
$conversations = [];
foreach ($msgs as $m) {
    $otherId = (string) $m['senderId'] === (string) $me['_id'] ? $m['recipientId'] : $m['senderId'];
    $key = (string) $otherId;
    if (!isset($conversations[$key])) {
        $conversations[$key] = ['otherId' => $otherId, 'lastMessage' => $m];
    }
}

// Fetch the partner user docs in one query.
$otherIds = array_map(fn($c) => $c['otherId'], $conversations);
$otherUsers = [];
if (!empty($otherIds)) {
    foreach ($db->users->find(['_id' => ['$in' => $otherIds]]) as $u) {
        $otherUsers[(string) $u['_id']] = $u;
    }
}

$pageTitle = 'Messages';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header"><h2>Messages</h2></div>

        <div style="padding:16px;">
            <form action="userlist.php" method="get">
                <button type="submit" class="btn secondary" style="width:auto; padding:8px 16px;">Find someone to message</button>
            </form>
        </div>

        <?php if (empty($conversations)): ?>
            <div class="empty-state"><p>No conversations yet.</p></div>
        <?php else: ?>
            <?php foreach ($conversations as $c):
                $otherUser = $otherUsers[(string) $c['otherId']] ?? null;
                if (!$otherUser) continue;
                $last = $c['lastMessage'];
            ?>
                <a class="conversation-list-item" href="conversation.php?id=<?php echo h((string) $otherUser['_id']); ?>">
                    <img class="avatar" src="<?php echo h($otherUser['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                    <div style="flex:1; min-width:0;">
                        <div><b>@<?php echo h($otherUser['username']); ?></b> <span class="timestamp"><?php echo h(time_ago($last['created'])); ?></span></div>
                        <div style="color:var(--text-dim); font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?php echo (string) $last['senderId'] === (string) $me['_id'] ? 'You: ' : ''; ?><?php echo h($last['body']); ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <aside class="right-col"></aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
