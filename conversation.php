<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'messages';

$otherId = to_object_id($_GET['id'] ?? '');
if (!$otherId) {
    redirect('messages.php');
}

$other = $db->users->findOne(['_id' => $otherId]);
if (!$other || (string) $other['_id'] === (string) $me['_id']) {
    redirect('messages.php');
}

$msgs = iterator_to_array($db->messages->find(
    ['$or' => [
        ['senderId' => $me['_id'], 'recipientId' => $otherId],
        ['senderId' => $otherId, 'recipientId' => $me['_id']],
    ]],
    ['sort' => ['created' => 1], 'limit' => 200]
));

// Mark incoming messages as read.
$db->messages->updateMany(
    ['senderId' => $otherId, 'recipientId' => $me['_id'], 'read' => false],
    ['$set' => ['read' => true]]
);

$pageTitle = '@' . ($other['username'] ?? '');
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header">
            <h2>@<?php echo h($other['username']); ?></h2>
        </div>

        <div class="msg-thread">
            <?php if (empty($msgs)): ?>
                <div class="empty-state"><p>Say hello to @<?php echo h($other['username']); ?>!</p></div>
            <?php endif; ?>
            <?php foreach ($msgs as $m): $mine = (string) $m['senderId'] === (string) $me['_id']; ?>
                <div class="msg-bubble <?php echo $mine ? 'mine' : 'theirs'; ?>">
                    <?php echo nl2br(h($m['body'])); ?>
                    <div class="msg-time"><?php echo h(time_ago($m['created'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="msg-form" action="send_message.php" method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="recipient_id" value="<?php echo h((string) $other['_id']); ?>">
            <input type="text" name="body" placeholder="Start a message" maxlength="1000" required autocomplete="off">
            <button type="submit" class="btn" style="width:auto;">Send</button>
        </form>
    </main>

    <aside class="right-col"></aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
