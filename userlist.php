<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'userlist';

$users = iterator_to_array($db->users->find([], ['sort' => ['username' => 1]]));
$followingIds = array_map(
    fn($d) => (string) $d['followingId'],
    iterator_to_array($db->following->find(['follower' => $me['_id']]))
);

$pageTitle = 'Users';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header"><h2>All Users</h2></div>

        <?php foreach ($users as $u):
            if ((string) $u['_id'] === (string) $me['_id']) continue;
            $isFollowing = in_array((string) $u['_id'], $followingIds, true);
        ?>
            <div class="user-item">
                <a href="profile.php?id=<?php echo h((string) $u['_id']); ?>">
                    <img class="avatar-sm" src="<?php echo h($u['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                </a>
                <div class="user-meta">
                    <a class="username" href="profile.php?id=<?php echo h((string) $u['_id']); ?>">@<?php echo h($u['username']); ?></a>
                    <?php if (!empty($u['bio'])): ?><div class="bio"><?php echo h($u['bio']); ?></div><?php endif; ?>
                </div>
                <form method="post" action="follow.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo h((string) $u['_id']); ?>">
                    <input type="hidden" name="redirect" value="userlist.php">
                    <button type="submit" class="follow-btn <?php echo $isFollowing ? 'following' : 'not-following'; ?>">
                        <?php echo $isFollowing ? 'Following' : 'Follow'; ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
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
