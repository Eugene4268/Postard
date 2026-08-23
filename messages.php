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
$otherIds = [];
foreach ($conversations as $conversation) {
    $otherId = to_object_id($conversation['otherId']);
    if ($otherId) {
        $otherIds[] = $otherId;
    }
}
$otherUsers = [];
if (!empty($otherIds)) {
    foreach ($db->users->find(['_id' => ['$in' => $otherIds]]) as $u) {
        $otherUsers[(string) $u['_id']] = $u;
    }
}

$selectedUser = null;
$selectedMessages = [];
$selectedId = to_object_id($_GET['id'] ?? '');
if (!$selectedId && !empty($conversations)) {
    $firstConversation = reset($conversations);
    $selectedId = to_object_id($firstConversation['otherId']);
}
if ($selectedId) {
    $selectedUser = $otherUsers[(string) $selectedId] ?? $db->users->findOne(['_id' => $selectedId]);
    if ($selectedUser) {
        $selectedMessages = iterator_to_array($db->messages->find(
            ['$or' => [
                ['senderId' => $me['_id'], 'recipientId' => $selectedId],
                ['senderId' => $selectedId, 'recipientId' => $me['_id']],
            ]],
            ['sort' => ['created' => 1], 'limit' => 200]
        ));
        $db->messages->updateMany(
            ['senderId' => $selectedId, 'recipientId' => $me['_id'], 'read' => false],
            ['$set' => ['read' => true]]
        );
    }
}

$pageTitle = 'Messages';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="messages-header"><h2>Messages</h2></div>
        <div class="messages-layout">
            <section class="conversation-pane">
                <form class="message-search" action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form>
                <div class="conversation-list">
                    <?php if (empty($conversations)): ?>
                        <div class="messages-empty">No conversations yet.</div>
                    <?php else: ?>
                        <?php foreach ($conversations as $c):
                            $otherUser = $otherUsers[(string) $c['otherId']] ?? null;
                            if (!$otherUser) continue;
                            $last = $c['lastMessage'];
                            $isSelected = $selectedUser && (string) $otherUser['_id'] === (string) $selectedUser['_id'];
                        ?>
                            <a class="conversation-list-item <?php echo $isSelected ? 'selected' : ''; ?>" href="messages.php?id=<?php echo h((string) $otherUser['_id']); ?>">
                                <img class="avatar" src="<?php echo h($otherUser['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                                <div class="conversation-meta">
                                    <div><b><?php echo h($otherUser['displayName'] ?? $otherUser['username']); ?></b><span class="timestamp"><?php echo h(time_ago($last['created'])); ?></span></div>
                                    <div class="conversation-preview"><?php echo (string) $last['senderId'] === (string) $me['_id'] ? 'You: ' : ''; ?><?php echo h($last['body']); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            <section class="message-pane">
                <?php if (!$selectedUser): ?>
                    <div class="messages-empty">Select a conversation to start messaging.</div>
                <?php else: ?>
                    <header class="message-contact"><a class="message-contact-profile" href="profile.php?id=<?php echo h((string) $selectedUser['_id']); ?>"><img class="avatar" src="<?php echo h($selectedUser['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""><span><h3><?php echo h($selectedUser['displayName'] ?? $selectedUser['username']); ?></h3><small>@<?php echo h($selectedUser['username']); ?></small></span></a><div class="message-contact-actions"><button type="button" class="chat-search-toggle" aria-label="Search conversation" aria-expanded="false"><i data-lucide="search"></i></button><a class="chat-info-link" href="profile.php?id=<?php echo h((string) $selectedUser['_id']); ?>" aria-label="View profile"><i data-lucide="info"></i></a></div></header>
                    <div class="chat-search-bar" hidden><i data-lucide="search"></i><input type="search" placeholder="Search this conversation" aria-label="Search this conversation"></div>
                    <div class="message-history">
                        <?php if (empty($selectedMessages)): ?><div class="messages-empty">Say hello to @<?php echo h($selectedUser['username']); ?>!</div><?php endif; ?>
                        <?php foreach ($selectedMessages as $message): $mine = (string) $message['senderId'] === (string) $me['_id']; ?>
                            <div class="message-entry <?php echo $mine ? 'mine' : 'theirs'; ?>">
                                <?php if (!$mine): ?><img class="avatar-sm" src="<?php echo h($selectedUser['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""><?php endif; ?>
                                <div><div class="msg-bubble <?php echo $mine ? 'sent' : 'received'; ?>"><?php echo nl2br(h($message['body'])); ?></div><small><?php echo h(time_ago($message['created'])); ?></small></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form class="msg-form" action="send_message.php" method="post">
                        <?php echo csrf_field(); ?><input type="hidden" name="recipient_id" value="<?php echo h((string) $selectedUser['_id']); ?>"><input type="text" name="body" placeholder="Write a message..." maxlength="1000" required autocomplete="off"><button type="submit" aria-label="Send message"><i data-lucide="send"></i></button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <aside class="right-col"></aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
