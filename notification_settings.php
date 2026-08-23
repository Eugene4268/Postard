<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'settings';
$defaults = [
    'push' => true, 'email' => true, 'inApp' => true, 'pauseAll' => false,
    'someoneFollows' => true, 'someoneLikes' => true, 'someoneComments' => true, 'someoneReplies' => true, 'someoneMentions' => true, 'someoneTags' => true, 'someoneShares' => true,
    'newMessages' => true, 'messageRequests' => true, 'groupMessages' => true, 'messageReactions' => true,
    'newFollowers' => true, 'followRequests' => true, 'suggestedAccounts' => false, 'peopleYouMayKnow' => false,
    'announcements' => true, 'newFeatures' => true, 'tips' => false, 'promotional' => false,
    'quietMode' => false, 'quietStart' => 22, 'quietEnd' => 7,
];
$preferences = array_merge($defaults, (array) ($me['notificationPreferences'] ?? []));
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $preferences = [];
    foreach (array_keys($defaults) as $key) {
        $preferences[$key] = in_array($key, ['quietStart', 'quietEnd'], true) ? max(0, min(23, (int) ($_POST[$key] ?? $defaults[$key]))) : isset($_POST[$key]);
    }
    $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['notificationPreferences' => $preferences]]);
    $success = 'Notification preferences saved.';
}
function notification_toggle($key, $checked) { return '<input class="privacy-toggle-input" type="checkbox" name="' . h($key) . '"' . ($checked ? ' checked' : '') . '><span class="privacy-toggle" aria-hidden="true"></span>'; }
function notification_section($title, $items, $preferences) {
    echo '<section class="privacy-section notification-settings-section"><h3>' . h($title) . '</h3>';
    foreach ($items as $key => $label) echo '<label class="privacy-row"><span>' . h($label) . '</span><span class="privacy-control">' . notification_toggle($key, !empty($preferences[$key])) . '</span></label>';
    echo '</section>';
}
$pageTitle = 'Notifications';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
<?php include __DIR__ . '/partials/navbar.php'; ?>
<main class="main privacy-main">
<header class="settings-header"><h2>Notifications</h2><p>Choose what you want to be notified about.</p></header>
<?php if ($error): ?><div class="error-msg account-message"><?php echo h($error); ?></div><?php endif; ?><?php if ($success): ?><div class="success-msg account-message"><?php echo h($success); ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
<?php notification_section('General', ['push' => 'Push Notifications', 'email' => 'Email Notifications', 'inApp' => 'In-App Notifications', 'pauseAll' => 'Pause All Notifications'], $preferences); ?>
<?php notification_section('Activity', ['someoneFollows' => 'Someone follows me', 'someoneLikes' => 'Someone likes my post', 'someoneComments' => 'Someone comments on my post', 'someoneReplies' => 'Someone replies to my comment', 'someoneMentions' => 'Someone mentions me', 'someoneTags' => 'Someone tags me', 'someoneShares' => 'Someone shares my post'], $preferences); ?>
<?php notification_section('Messages', ['newMessages' => 'New Messages', 'messageRequests' => 'Message Requests', 'groupMessages' => 'Group Messages', 'messageReactions' => 'Message Reactions'], $preferences); ?>
<?php notification_section('People', ['newFollowers' => 'New Followers', 'followRequests' => 'Follow Requests', 'suggestedAccounts' => 'Suggested Accounts', 'peopleYouMayKnow' => 'People You May Know'], $preferences); ?>
<?php notification_section('Postard Updates', ['announcements' => 'Announcements', 'newFeatures' => 'New Features', 'tips' => 'Tips & Tutorials', 'promotional' => 'Promotional Notifications'], $preferences); ?>
<section class="privacy-section notification-settings-section"><h3>Quiet Hours</h3><label class="privacy-row"><span>Quiet Mode</span><span class="privacy-control"><?php echo notification_toggle('quietMode', $preferences['quietMode']); ?></span></label><p class="quiet-description">Notifications will be paused during your selected hours.</p><div class="quiet-hours"><label>Start <input type="time" name="quietStart" value="<?php echo h(sprintf('%02d:00', $preferences['quietStart'])); ?>"></label><label>End <input type="time" name="quietEnd" value="<?php echo h(sprintf('%02d:00', $preferences['quietEnd'])); ?>"></label></div></section>
<div class="privacy-actions"><button class="account-button" type="submit">Save Notification Settings</button></div>
</form></main><aside class="right-col"><div class="widget search-box"><form action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form></div></aside></div><script src="assets/js/app.js"></script></body></html>
