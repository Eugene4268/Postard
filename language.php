<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'settings';
$defaults = ['appLanguage' => 'English', 'contentLanguages' => ['English', 'Hindi'], 'translatePosts' => true, 'translateInto' => 'English', 'showOriginal' => true, 'detectLanguage' => true, 'region' => 'India', 'dateFormat' => 'DD/MM/YYYY', 'timeFormat' => '12-hour'];
$preferences = array_merge($defaults, (array) ($me['languagePreferences'] ?? []));
$languages = ['English', 'Hindi', 'Marathi', 'Spanish', 'French'];
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $contentLanguages = array_values(array_intersect($languages, (array) ($_POST['content_languages'] ?? [])));
    if (!$contentLanguages) $contentLanguages = ['English'];
    $preferences = [
        'appLanguage' => in_array($_POST['app_language'] ?? '', $languages, true) ? $_POST['app_language'] : 'English',
        'contentLanguages' => $contentLanguages, 'translatePosts' => isset($_POST['translate_posts']),
        'translateInto' => in_array($_POST['translate_into'] ?? '', $languages, true) ? $_POST['translate_into'] : 'English',
        'showOriginal' => isset($_POST['show_original']), 'detectLanguage' => isset($_POST['detect_language']),
        'region' => in_array($_POST['region'] ?? '', ['India', 'United States', 'United Kingdom'], true) ? $_POST['region'] : 'India',
        'dateFormat' => in_array($_POST['date_format'] ?? '', ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'], true) ? $_POST['date_format'] : 'DD/MM/YYYY',
        'timeFormat' => in_array($_POST['time_format'] ?? '', ['12-hour', '24-hour'], true) ? $_POST['time_format'] : '12-hour',
    ];
    $db->users->updateOne(['_id' => $me['_id']], ['$set' => ['languagePreferences' => $preferences]]);
    $success = 'Language preferences saved.';
}
function language_toggle($key, $checked) { return '<input class="privacy-toggle-input" type="checkbox" name="' . h($key) . '"' . ($checked ? ' checked' : '') . '><span class="privacy-toggle" aria-hidden="true"></span>'; }
function language_select($key, $current, $options) { $html = '<select name="' . h($key) . '">'; foreach ($options as $option) $html .= '<option' . ($current === $option ? ' selected' : '') . '>' . h($option) . '</option>'; return $html . '</select>'; }
$pageTitle = 'Language';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
<?php include __DIR__ . '/partials/navbar.php'; ?>
<main class="main privacy-main language-main">
<header class="settings-header"><h2>Language</h2><p>Choose the language you want to use across Postard.</p></header>
<?php if ($success): ?><div class="success-msg account-message"><?php echo h($success); ?></div><?php endif; ?><?php if ($error): ?><div class="error-msg account-message"><?php echo h($error); ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
<section class="privacy-section language-section"><h3>App Language</h3><p class="content-help">Choose the language used throughout the Postard interface.</p><?php foreach ($languages as $language): ?><label class="language-choice"><span><?php echo h($language); ?></span><input type="radio" name="app_language" value="<?php echo h($language); ?>"<?php echo $preferences['appLanguage'] === $language ? ' checked' : ''; ?>><b><?php echo $preferences['appLanguage'] === $language ? '✓' : '›'; ?></b></label><?php endforeach; ?></section>
<section class="privacy-section language-section"><h3>Content Languages</h3><p class="content-help">Choose languages you'd like to see in your feed.</p><?php foreach ($languages as $language): ?><label class="language-choice"><span><?php echo h($language); ?></span><input type="checkbox" name="content_languages[]" value="<?php echo h($language); ?>"<?php echo in_array($language, $preferences['contentLanguages'], true) ? ' checked' : ''; ?>><b><?php echo in_array($language, $preferences['contentLanguages'], true) ? '✓' : '○'; ?></b></label><?php endforeach; ?></section>
<section class="privacy-section language-section"><h3>Translation</h3><label class="privacy-row"><span>Translate Posts</span><span class="privacy-control"><?php echo language_toggle('translate_posts', $preferences['translatePosts']); ?></span></label><label class="privacy-row"><span>Translate Into</span><?php echo language_select('translate_into', $preferences['translateInto'], $languages); ?></label><label class="privacy-row"><span>Show Original Text</span><span class="privacy-control"><?php echo language_toggle('show_original', $preferences['showOriginal']); ?></span></label></section>
<section class="privacy-section language-section"><h3>Language Detection</h3><label class="privacy-row"><span>Automatically Detect Language</span><span class="privacy-control"><?php echo language_toggle('detect_language', $preferences['detectLanguage']); ?></span></label><p class="content-help">Postard will automatically detect the language of posts and comments.</p></section>
<section class="privacy-section language-section"><h3>Regional Format</h3><label class="privacy-row"><span>Region</span><?php echo language_select('region', $preferences['region'], ['India', 'United States', 'United Kingdom']); ?></label><label class="privacy-row"><span>Date Format</span><?php echo language_select('date_format', $preferences['dateFormat'], ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD']); ?></label><label class="privacy-row"><span>Time Format</span><?php echo language_select('time_format', $preferences['timeFormat'], ['12-hour', '24-hour']); ?></label></section>
<div class="privacy-actions"><button class="account-button" type="submit">Save Language Preferences</button></div>
</form></main><aside class="right-col"><div class="widget search-box"><form action="search.php" method="get"><i data-lucide="search"></i><input type="text" name="q" placeholder="Search Postard"></form></div></aside></div><script src="assets/js/app.js"></script></body></html>
