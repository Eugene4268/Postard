<?php
// Expects: $me (current user document) available in scope.
$unread = unread_notification_count($me['_id']);
?>
<nav class="sidebar">
    <a href="home.php" class="brand">Postard</a>
    <a href="home.php" class="nav-link <?php echo ($active ?? '') === 'home' ? 'active' : ''; ?>">
        <span class="nav-icon">🏠</span> Home
    </a>
    <a href="explore.php" class="nav-link <?php echo ($active ?? '') === 'explore' ? 'active' : ''; ?>">
        <span class="nav-icon">🔍</span> Explore
    </a>
    <a href="notifications.php" class="nav-link <?php echo ($active ?? '') === 'notifications' ? 'active' : ''; ?>">
        <span class="nav-icon">🔔</span> Notifications
        <?php if ($unread > 0): ?><span class="badge"><?php echo (int) $unread; ?></span><?php endif; ?>
    </a>
    <a href="messages.php" class="nav-link <?php echo ($active ?? '') === 'messages' ? 'active' : ''; ?>">
        <span class="nav-icon">✉️</span> Messages
    </a>
    <a href="profile.php?id=<?php echo (string) $me['_id']; ?>" class="nav-link <?php echo ($active ?? '') === 'profile' ? 'active' : ''; ?>">
        <span class="nav-icon">👤</span> Profile
    </a>
    <a href="userlist.php" class="nav-link <?php echo ($active ?? '') === 'userlist' ? 'active' : ''; ?>">
        <span class="nav-icon">👥</span> Users
    </a>
    <a href="logout.php" class="nav-link logout-link">
        <span class="nav-icon">🚪</span> Logout
    </a>
    <div class="nav-user">
        <img class="avatar-sm" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
        <span>@<?php echo h($me['username']); ?></span>
    </div>
</nav>
