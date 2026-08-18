<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'explore';
$q = trim($_GET['q'] ?? '');

$users = [];
$tweets = [];

if ($q !== '') {
    // Escape user input before building a regex to avoid ReDoS / regex injection.
    $safe = preg_quote($q, '/');

    $users = iterator_to_array($db->users->find(
        ['username' => ['$regex' => $safe, '$options' => 'i']],
        ['limit' => 20]
    ));

    $tweets = iterator_to_array($db->tweets->find(
        ['body' => ['$regex' => $safe, '$options' => 'i']],
        ['sort' => ['created' => -1], 'limit' => 30]
    ));
}

$pageTitle = 'Search';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header">
            <h2>Search</h2>
        </div>
        <div style="padding:16px;">
            <form action="search.php" method="get">
                <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search Postard" style="width:100%; padding:10px 16px; border-radius:999px; background:var(--bg-elevated); border:1px solid var(--border); color:var(--text);">
            </form>
        </div>

        <?php if ($q === ''): ?>
            <div class="empty-state"><p>Search for people or posts.</p></div>
        <?php else: ?>
            <?php if (!empty($users)): ?>
                <div class="tabs"><div class="tab active">People</div></div>
                <?php foreach ($users as $u): ?>
                    <div class="user-item">
                        <img class="avatar-sm" src="<?php echo h($u['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                        <div class="user-meta">
                            <a class="username" href="profile.php?id=<?php echo h((string) $u['_id']); ?>">@<?php echo h($u['username']); ?></a>
                            <?php if (!empty($u['bio'])): ?><div class="bio"><?php echo h($u['bio']); ?></div><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($tweets)): ?>
                <div class="tabs"><div class="tab active">Posts</div></div>
                <?php foreach ($tweets as $tweet): include __DIR__ . '/partials/tweet_card.php'; endforeach; ?>
            <?php endif; ?>

            <?php if (empty($users) && empty($tweets)): ?>
                <div class="empty-state"><p>No results for "<?php echo h($q); ?>".</p></div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <aside class="right-col"></aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
