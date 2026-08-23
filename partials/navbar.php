<?php
// Expects: $me (current user document) available in scope.
$unread = unread_notification_count($me['_id']);
?>
<nav class="sidebar">
    <a href="home.php" class="brand"><img src="assets/img/postard_logo.png" alt="Postard"></a>
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
    <a id="settings" href="settings.php" class="nav-link nav-settings <?php echo ($active ?? '') === 'settings' ? 'active' : ''; ?>"><span class="nav-icon"><i data-lucide="settings"></i></span> Settings</a>
    <button type="button" class="nav-link nav-theme"><span class="nav-icon"><i data-lucide="moon"></i></span> Theme</button>
    <a href="home.php#compose" class="create-post"><i data-lucide="plus"></i> Create Post</a>
    <a href="logout.php" class="nav-link logout-link">
        <span class="nav-icon">🚪</span> Logout
    </a>
    <a class="nav-user" href="edit_profile.php">
        <img class="avatar-sm" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
        <span class="nav-user-meta"><b><?php echo h($me['displayName'] ?? $me['username']); ?> <i data-lucide="badge-check"></i></b><small>@<?php echo h($me['username']); ?></small></span>
    </a>
    <div id="user-menu" class="user-menu" hidden>
        <a href="profile.php?id=<?php echo h((string) $me['_id']); ?>"><i data-lucide="user-round"></i> Edit Profile</a>
        <a href="settings.php" class="user-menu-settings"><i data-lucide="settings"></i> Settings</a>
        <a href="logout.php" class="user-menu-logout"><i data-lucide="log-out"></i> Logout</a>
    </div>
</nav>
