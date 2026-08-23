<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'settings';
$defaults = [
    'profileVisibility' => 'Public', 'postAudience' => 'Everyone', 'allowSharing' => true, 'allowDownloads' => false,
    'messageWho' => 'Followers', 'commentWho' => 'Everyone', 'mentionWho' => 'Followers', 'tagWho' => 'Everyone',
    'showInSearch' => true, 'findByEmail' => false, 'findByPhone' => false, 'showOnline' => true,
];
$privacy = array_merge($defaults, (array) ($me['privacy'] ?? []));
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';
    if ($action === 'clear_search') {
        $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['searchHistory' => []]]);
        $success = 'Search history cleared.';
    } elseif ($action === 'download_data') {
        $tweets = iterator_to_array($db->tweets->find(['authorId' => $me['_id']], ['projection' => ['body' => 1, 'created' => 1]]));
        $data = ['profile' => ['username' => $me['username'], 'displayName' => $me['displayName'] ?? '', 'email' => $me['email'] ?? ''], 'posts' => $tweets, 'privacy' => $privacy];
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="postard-data.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    } else {
        $privacy = [
            'profileVisibility' => in_array($_POST['profile_visibility'] ?? '', ['Public', 'Followers only'], true) ? $_POST['profile_visibility'] : $defaults['profileVisibility'],
            'postAudience' => in_array($_POST['post_audience'] ?? '', ['Everyone', 'Followers'], true) ? $_POST['post_audience'] : $defaults['postAudience'],
            'allowSharing' => isset($_POST['allow_sharing']), 'allowDownloads' => isset($_POST['allow_downloads']),
            'messageWho' => in_array($_POST['message_who'] ?? '', ['Everyone', 'Followers', 'No one'], true) ? $_POST['message_who'] : $defaults['messageWho'],
            'commentWho' => in_array($_POST['comment_who'] ?? '', ['Everyone', 'Followers', 'No one'], true) ? $_POST['comment_who'] : $defaults['commentWho'],
            'mentionWho' => in_array($_POST['mention_who'] ?? '', ['Everyone', 'Followers', 'No one'], true) ? $_POST['mention_who'] : $defaults['mentionWho'],
            'tagWho' => in_array($_POST['tag_who'] ?? '', ['Everyone', 'Followers', 'No one'], true) ? $_POST['tag_who'] : $defaults['tagWho'],
            'showInSearch' => isset($_POST['show_in_search']), 'findByEmail' => isset($_POST['find_by_email']), 'findByPhone' => isset($_POST['find_by_phone']), 'showOnline' => isset($_POST['show_online']),
        ];
        $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['privacy' => $privacy]]);
        $success = 'Privacy settings saved.';
    }
}
function privacy_select($name, $current, $options) {
    $html = '<select name="' . h($name) . '">';
    foreach ($options as $option) $html .= '<option ' . ($current === $option ? 'selected' : '') . '>' . h($option) . '</option>';
    return $html . '</select>';
}
function privacy_toggle($name, $checked) { return '<input class="privacy-toggle-input" type="checkbox" name="' . h($name) . '"' . ($checked ? ' checked' : '') . '><span class="privacy-toggle" aria-hidden="true"></span>'; }
$pageTitle = 'Privacy';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
<?php include __DIR__ . '/partials/navbar.php'; ?>
<main class="main privacy-main">
<header class="settings-header"><h2>Privacy</h2><p>Control who can see your content and interact with you.</p></header>
<?php if ($error): ?><div class="error-msg account-message"><?php echo h($error); ?></div><?php endif; ?><?php if ($success): ?><div class="success-msg account-message"><?php echo h($success); ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
<section class="privacy-section"><h3>Profile Visibility</h3><label class="privacy-row"><span>Profile Visibility</span><?php echo privacy_select('profile_visibility', $privacy['profileVisibility'], ['Public', 'Followers only']); ?></label></section>
<section class="privacy-section"><h3>Content Privacy</h3><label class="privacy-row"><span>Default Post Audience</span><?php echo privacy_select('post_audience', $privacy['postAudience'], ['Everyone', 'Followers']); ?></label><label class="privacy-row"><span>Allow Post Sharing</span><span class="privacy-control"><?php echo privacy_toggle('allow_sharing', $privacy['allowSharing']); ?></span></label><label class="privacy-row"><span>Allow Post Downloads</span><span class="privacy-control"><?php echo privacy_toggle('allow_downloads', $privacy['allowDownloads']); ?></span></label></section>
<section class="privacy-section"><h3>Interactions</h3><label class="privacy-row"><span>Who can message me</span><?php echo privacy_select('message_who', $privacy['messageWho'], ['Everyone', 'Followers', 'No one']); ?></label><label class="privacy-row"><span>Who can comment</span><?php echo privacy_select('comment_who', $privacy['commentWho'], ['Everyone', 'Followers', 'No one']); ?></label><label class="privacy-row"><span>Who can mention me</span><?php echo privacy_select('mention_who', $privacy['mentionWho'], ['Everyone', 'Followers', 'No one']); ?></label><label class="privacy-row"><span>Who can tag me</span><?php echo privacy_select('tag_who', $privacy['tagWho'], ['Everyone', 'Followers', 'No one']); ?></label></section>
<section class="privacy-section"><h3>Discovery</h3><label class="privacy-row"><span>Show profile in search</span><span class="privacy-control"><?php echo privacy_toggle('show_in_search', $privacy['showInSearch']); ?></span></label><label class="privacy-row"><span>Find me by email</span><span class="privacy-control"><?php echo privacy_toggle('find_by_email', $privacy['findByEmail']); ?></span></label><label class="privacy-row"><span>Find me by phone</span><span class="privacy-control"><?php echo privacy_toggle('find_by_phone', $privacy['findByPhone']); ?></span></label><label class="privacy-row"><span>Show online status</span><span class="privacy-control"><?php echo privacy_toggle('show_online', $privacy['showOnline']); ?></span></label></section>
<div class="privacy-actions"><button class="account-button" type="submit">Save Privacy Settings</button></div>
</form>
<section class="privacy-section"><h3>Blocked &amp; Restricted</h3><a class="privacy-row privacy-link" href="userlist.php"><span>Blocked Accounts</span><span>Manage <b>›</b></span></a><a class="privacy-row privacy-link" href="userlist.php"><span>Restricted Accounts</span><span>Manage <b>›</b></span></a></section>
<section class="privacy-section"><h3>Your Data</h3><form method="post" class="privacy-data-row"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="action" value="download_data"><span>Download My Data</span><button type="submit">Download <b>›</b></button></form><form method="post" class="privacy-data-row"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="action" value="clear_search"><span>Clear Search History</span><button type="submit">Clear</button></form></section>
</main>
<aside class="right-col"><div class="widget search-box"><form action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form></div></aside>
</div><script src="assets/js/app.js"></script></body></html>
