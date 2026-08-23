<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'settings';
$defaults = ['defaultFeed' => 'recommended', 'showRecommended' => true, 'showFollowing' => true, 'showPopular' => true, 'interests' => [], 'mutedWords' => '', 'mutedAccounts' => '', 'sensitiveContent' => 'Standard', 'contentLanguage' => 'English', 'translate' => true, 'autoplayVideos' => true, 'autoplayGifs' => true, 'highQuality' => true, 'dataSaver' => false, 'personalized' => true, 'recommendedAccounts' => true, 'trending' => true, 'locationBased' => false];
$preferences = array_merge($defaults, (array) ($me['contentPreferences'] ?? []));
$topics = ['Technology', 'Gaming', 'Sports', 'Music', 'Movies', 'Travel', 'Art', 'Education', 'Science'];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $selectedTopics = array_values(array_intersect($topics, (array) ($_POST['interests'] ?? [])));
    $preferences = [
        'defaultFeed' => in_array($_POST['default_feed'] ?? '', ['recommended', 'following', 'popular'], true) ? $_POST['default_feed'] : 'recommended',
        'showRecommended' => isset($_POST['show_recommended']), 'showFollowing' => isset($_POST['show_following']), 'showPopular' => isset($_POST['show_popular']),
        'interests' => $selectedTopics, 'mutedWords' => trim($_POST['muted_words'] ?? ''), 'mutedAccounts' => trim($_POST['muted_accounts'] ?? ''),
        'sensitiveContent' => in_array($_POST['sensitive_content'] ?? '', ['Less', 'Standard', 'More'], true) ? $_POST['sensitive_content'] : 'Standard',
        'contentLanguage' => in_array($_POST['content_language'] ?? '', ['English', 'Hindi', 'Spanish', 'French'], true) ? $_POST['content_language'] : 'English',
        'translate' => isset($_POST['translate']), 'autoplayVideos' => isset($_POST['autoplay_videos']), 'autoplayGifs' => isset($_POST['autoplay_gifs']), 'highQuality' => isset($_POST['high_quality']), 'dataSaver' => isset($_POST['data_saver']),
        'personalized' => isset($_POST['personalized']), 'recommendedAccounts' => isset($_POST['recommended_accounts']), 'trending' => isset($_POST['trending']), 'locationBased' => isset($_POST['location_based']),
    ];
    $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['contentPreferences' => $preferences]]);
    $success = 'Content preferences saved.';
}
function content_toggle($key, $checked) { return '<input class="privacy-toggle-input" type="checkbox" name="' . h($key) . '"' . ($checked ? ' checked' : '') . '><span class="privacy-toggle" aria-hidden="true"></span>'; }
function content_select($key, $current, $options) { $html = '<select name="' . h($key) . '">'; foreach ($options as $value => $label) { if (is_int($value)) $value = $label; $html .= '<option value="' . h($value) . '"' . ($current === $value ? ' selected' : '') . '>' . h($label) . '</option>'; } return $html . '</select>'; }
function content_section($title, $rows, $preferences) { if ($title !== '') echo '<section class="privacy-section content-section"><h3>' . h($title) . '</h3>'; foreach ($rows as $key => $label) echo '<label class="privacy-row"><span>' . h($label) . '</span><span class="privacy-control">' . content_toggle($key, !empty($preferences[$key])) . '</span></label>'; if ($title !== '') echo '</section>'; }
$pageTitle = 'Content Preferences';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
<?php include __DIR__ . '/partials/navbar.php'; ?>
<main class="main privacy-main">
<header class="settings-header"><h2>Content Preferences</h2><p>Customize what you see across your Postard experience.</p></header>
<?php if ($success): ?><div class="success-msg account-message"><?php echo h($success); ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
<section class="privacy-section content-section"><h3>Feed Preferences</h3><label class="privacy-row"><span>Default Feed</span><?php echo content_select('default_feed', $preferences['defaultFeed'], ['recommended' => 'Recommended', 'following' => 'Following', 'popular' => 'Popular']); ?></label><?php content_section('', ['showRecommended' => 'Show Recommended Posts', 'showFollowing' => 'Show Posts From Following', 'showPopular' => 'Show Popular Posts'], $preferences); ?></section>
<section class="privacy-section content-section"><h3>Your Interests</h3><p class="content-help">Choose topics you'd like to see more of.</p><div class="interest-grid"><?php foreach ($topics as $topic): ?><label class="interest-chip"><input type="checkbox" name="interests[]" value="<?php echo h($topic); ?>"<?php echo in_array($topic, $preferences['interests'], true) ? ' checked' : ''; ?>><span><?php echo h($topic); ?></span></label><?php endforeach; ?></div><label class="privacy-row privacy-link"><span>Manage Interests</span><span>Saved above <b>›</b></span></label></section>
<section class="privacy-section content-section"><h3>Content Filtering</h3><label class="privacy-row"><span>Muted Words &amp; Topics</span><input class="content-text-input" type="text" name="muted_words" value="<?php echo h($preferences['mutedWords']); ?>" placeholder="comma-separated"></label><label class="privacy-row"><span>Muted Accounts</span><input class="content-text-input" type="text" name="muted_accounts" value="<?php echo h($preferences['mutedAccounts']); ?>" placeholder="@username"></label><div class="sensitive-choice"><span>Sensitive Content</span><?php foreach (['Less' => 'Show less', 'Standard' => 'Standard', 'More' => 'Show more'] as $value => $label): ?><label><input type="radio" name="sensitive_content" value="<?php echo $value; ?>"<?php echo $preferences['sensitiveContent'] === $value ? ' checked' : ''; ?>> <?php echo $label; ?></label><?php endforeach; ?></div></section>
<section class="privacy-section content-section"><h3>Language</h3><label class="privacy-row"><span>Content Language</span><?php echo content_select('content_language', $preferences['contentLanguage'], ['English', 'Hindi', 'Spanish', 'French']); ?></label><label class="privacy-row"><span>Automatically Translate</span><span class="privacy-control"><?php echo content_toggle('translate', $preferences['translate']); ?></span></label></section>
<?php content_section('Media', ['autoplayVideos' => 'Autoplay Videos', 'autoplayGifs' => 'Autoplay GIFs', 'highQuality' => 'High-Quality Media', 'dataSaver' => 'Data Saver'], $preferences); ?>
<?php content_section('Recommendations', ['personalized' => 'Personalized Recommendations', 'recommendedAccounts' => 'Recommended Accounts', 'trending' => 'Trending Content', 'locationBased' => 'Location-Based Recommendations'], $preferences); ?>
<div class="privacy-actions"><button class="account-button" type="submit">Save Content Preferences</button></div>
</form></main><aside class="right-col"><div class="widget search-box"><form action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form></div></aside></div><script src="assets/js/app.js"></script></body></html>
