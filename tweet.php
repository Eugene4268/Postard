<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = '';

$tweetId = to_object_id($_GET['id'] ?? '');
if (!$tweetId) {
    redirect('home.php');
}

$mainTweet = $db->tweets->findOne(['_id' => $tweetId]);
if (!$mainTweet) {
    http_response_code(404);
    $pageTitle = 'Not found';
    include __DIR__ . '/partials/head.php';
    echo '<div class="app">';
    include __DIR__ . '/partials/navbar.php';
    echo '<main class="main"><div class="empty-state"><p>Post not found. It may have been deleted.</p></div></main></div></body></html>';
    exit;
}

$replies = iterator_to_array($db->tweets->find(
    ['replyTo' => $tweetId],
    ['sort' => ['created' => 1]]
));

$pageTitle = 'Post';
include __DIR__ . '/partials/head.php';

// Reuse the tweet card partial for the main tweet by aliasing the loop var.
$tweet = $mainTweet;
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header"><h2>Post</h2></div>

        <?php include __DIR__ . '/partials/tweet_card.php'; ?>

        <form class="composer composer-form" id="reply-box" action="create_tweet.php" method="post" enctype="multipart/form-data" data-maxlen="<?php echo MAX_TWEET_LENGTH; ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="reply_to" value="<?php echo h((string) $mainTweet['_id']); ?>">
            <img class="avatar" src="<?php echo h($me['avatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
            <div style="flex:1">
                <textarea name="body" placeholder="Post your reply" maxlength="<?php echo MAX_TWEET_LENGTH; ?>" required></textarea>
                <img class="image-preview">
                <div class="composer-footer">
                    <label class="file-label" title="Add image">
                        🖼️ <input type="file" name="image" accept="image/*" style="display:none">
                    </label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span class="char-count">0 / <?php echo MAX_TWEET_LENGTH; ?></span>
                        <button type="submit" class="btn" disabled>Reply</button>
                    </div>
                </div>
            </div>
        </form>

        <?php foreach ($replies as $tweet): include __DIR__ . '/partials/tweet_card.php'; endforeach; ?>
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
