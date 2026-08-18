<?php
require_once __DIR__ . '/config.php';
require_login();

$me = current_user();
$active = 'explore';

$tweets = iterator_to_array($db->tweets->find(
    ['retweetOf' => ['$exists' => false]],
    ['sort' => ['created' => -1], 'limit' => 50]
));

$pageTitle = 'Explore';
include __DIR__ . '/partials/head.php';
?>
<div class="app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="main">
        <div class="main-header"><h2>Explore</h2></div>

        <?php if (empty($tweets)): ?>
            <div class="empty-state"><p>No posts yet. Be the first!</p></div>
        <?php else: ?>
            <?php foreach ($tweets as $tweet): include __DIR__ . '/partials/tweet_card.php'; endforeach; ?>
        <?php endif; ?>
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
