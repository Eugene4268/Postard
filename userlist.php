<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'userlist';
$view = ($_GET['view'] ?? '') === 'following' ? 'following' : 'all';

$followingIds = array_map(
    fn($d) => (string) $d['followingId'],
    iterator_to_array($db->following->find(['follower' => $me['_id']]))
);
$userFilter = $view === 'following' ? ['_id' => ['$in' => array_map(fn($id) => new MongoDB\BSON\ObjectId($id), $followingIds)]] : [];
$users = iterator_to_array($db->users->find($userFilter, ['sort' => ['username' => 1]]));
$suggestedUsers = iterator_to_array($db->users->find(
    ['_id' => ['$nin' => array_merge([$me['_id']], array_map(fn($id) => new MongoDB\BSON\ObjectId($id), $followingIds))]],
    ['sort' => ['created' => -1, 'username' => 1], 'limit' => 3]
));

$pageTitle = 'Users';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <header class="users-header">
            <h2>Users</h2>
            <p>Discover and connect with people on Postard</p>
        </header>

        <nav class="users-tabs" aria-label="User filters">
            <a class="<?php echo $view === 'all' ? 'active' : ''; ?>" href="userlist.php">All Users</a>
            <a class="<?php echo $view === 'following' ? 'active' : ''; ?>" href="userlist.php?view=following">Following</a>
        </nav>

        <section class="users-list">
        <?php foreach ($users as $u):
            if ((string) $u['_id'] === (string) $me['_id']) continue;
            $isFollowing = in_array((string) $u['_id'], $followingIds, true);
        ?>
            <div class="user-item">
                <a href="profile.php?id=<?php echo h((string) $u['_id']); ?>">
                    <img class="avatar" src="<?php echo h($u['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
                </a>
                <div class="user-meta">
                    <a class="user-display-name" href="profile.php?id=<?php echo h((string) $u['_id']); ?>"><?php echo h($u['displayName'] ?? $u['username']); ?></a>
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
                <button type="button" class="user-more" aria-label="More options for <?php echo h($u['displayName'] ?? $u['username']); ?>"><i data-lucide="more-horizontal"></i></button>
            </div>
        <?php endforeach; ?>
        <?php if (empty($users) || count(array_filter($users, fn($u) => (string) $u['_id'] !== (string) $me['_id'])) === 0): ?>
            <div class="users-empty"><i data-lucide="users-round"></i><h3><?php echo $view === 'following' ? 'You are not following anyone yet' : 'Find more users'; ?></h3><p><?php echo $view === 'following' ? 'Follow people to build your network.' : 'Search for users or explore communities to connect.'; ?></p><a href="<?php echo $view === 'following' ? 'userlist.php' : 'explore.php'; ?>"><?php echo $view === 'following' ? 'Discover people' : 'Explore Communities'; ?></a></div>
        <?php endif; ?>
        </section>
        <?php if ($view === 'all' && !empty($users)): ?>
            <section class="users-discovery">
                <i data-lucide="users-round"></i>
                <h3>Find more users</h3>
                <p>Search for users or explore communities to connect.</p>
                <a href="explore.php">Explore Communities</a>
            </section>
        <?php endif; ?>
    </main>

    <aside class="right-col">
        <div class="widget search-box">
            <form action="search.php" method="get">
                <i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard">
            </form>
        </div>
        <section class="widget suggested-widget users-suggested">
            <div class="widget-title"><h3>Suggested for you</h3><a href="userlist.php">View all</a></div>
            <?php foreach ($suggestedUsers as $user): ?>
                <div class="mini-person">
                    <a class="mini-person-profile" href="profile.php?id=<?php echo h((string) $user['_id']); ?>"><img class="mini-avatar" src="<?php echo h($user['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt=""></a>
                    <a class="mini-person-name" href="profile.php?id=<?php echo h((string) $user['_id']); ?>"><b><?php echo h($user['displayName'] ?? $user['username']); ?></b><small>@<?php echo h($user['username']); ?></small></a>
                    <form method="post" action="follow.php"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo h((string) $user['_id']); ?>"><input type="hidden" name="redirect" value="userlist.php"><button type="submit">Follow</button></form>
                </div>
            <?php endforeach; ?>
        </section>
        <section class="widget invite-widget">
            <div class="invite-icon"><i data-lucide="mail"></i></div><div><h3>Invite friends</h3><p>Connect with your friends and grow your network.</p><a href="mailto:?subject=Join%20Postard">Invite friends</a></div>
        </section>
    </aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
