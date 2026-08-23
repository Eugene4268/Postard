<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'settings';
$pageTitle = 'Settings';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <header class="settings-header">
            <h2>Settings</h2>
            <p>Manage your account and preferences</p>
        </header>

        <section class="settings-list" aria-label="Settings options">
            <a class="settings-row" href="account.php">
                <span class="settings-icon"><i data-lucide="shield-check"></i></span>
                <span class="settings-copy"><strong>Account</strong><span>Password security and more</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="privacy.php">
                <span class="settings-icon"><i data-lucide="lock"></i></span>
                <span class="settings-copy"><strong>Privacy</strong><span>Manage your privacy settings</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="notification_settings.php">
                <span class="settings-icon"><i data-lucide="bell"></i></span>
                <span class="settings-copy"><strong>Notifications</strong><span>Choose what you want to see</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="content_preferences.php">
                <span class="settings-icon"><i data-lucide="sliders"></i></span>
                <span class="settings-copy"><strong>Content Preferences</strong><span>Manage your content feed</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="appearance.php">
                <span class="settings-icon"><i data-lucide="brush"></i></span>
                <span class="settings-copy"><strong>Appearance</strong><span>Customize theme and display</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="language.php">
                <span class="settings-icon"><i data-lucide="globe-2"></i></span>
                <span class="settings-copy"><strong>Language</strong><span>Choose your language</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="mailto:support@postard.local?subject=Postard%20support">
                <span class="settings-icon"><i data-lucide="help-circle"></i></span>
                <span class="settings-copy"><strong>Help &amp; Support</strong><span>Get help and contact support</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
            <a class="settings-row" href="README.md">
                <span class="settings-icon"><i data-lucide="info"></i></span>
                <span class="settings-copy"><strong>About Postard</strong><span>Version, terms and policies</span></span>
                <i class="settings-arrow" data-lucide="chevron-right"></i>
            </a>
        </section>
    </main>

    <aside class="right-col">
        <div class="widget search-box">
            <form action="search.php" method="get">
                <i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard">
            </form>
        </div>
        <section class="widget settings-aside">
            <i class="settings-aside-icon" data-lucide="settings"></i>
            <h3>Settings</h3>
            <p>Manage your preferences and customize your Postard experience</p>
        </section>
    </aside>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
