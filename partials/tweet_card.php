<?php
/**
 * Renders one tweet. Expects in scope:
 *   $tweet - the tweet document (may be a repost wrapper, see below)
 *   $me    - current user document
 *   $db    - MongoDB database
 *
 * A "repost" is stored as its own tweet doc with retweetOf = original tweet's _id.
 * When rendering, if $tweet has retweetOf set, we show "X reposted" above the original content.
 */

$isRepost = !empty($tweet['retweetOf']);
$display = $tweet;

if ($isRepost) {
    $original = $db->tweets->findOne(['_id' => $tweet['retweetOf']]);
    if (!$original) {
        return; // original was deleted
    }
    $display = $original;
}

$tweetId = (string) $display['_id'];
$liked = $db->likes->findOne(['tweetId' => $display['_id'], 'userId' => $me['_id']]) !== null;
$reposted = $db->tweets->findOne(['retweetOf' => $display['_id'], 'authorId' => $me['_id']]) !== null;
$isOwner = (string) $display['authorId'] === (string) $me['_id'];
?>
<article class="tweet" data-tweet-id="<?php echo h($tweetId); ?>">
    <?php if ($isRepost): ?>
        <div class="repost-label">🔁 <a href="profile.php?id=<?php echo h((string) $tweet['authorId']); ?>"><?php echo h($tweet['authorName']); ?></a> reposted</div>
    <?php endif; ?>
    <div class="tweet-row">
        <a href="profile.php?id=<?php echo h((string) $display['authorId']); ?>">
            <img class="avatar" src="<?php echo h($display['authorAvatar'] ?? 'assets/img/default-avatar.svg'); ?>" alt="">
        </a>
        <div class="tweet-body">
            <div class="tweet-head">
                <a class="author" href="profile.php?id=<?php echo h((string) $display['authorId']); ?>">@<?php echo h($display['authorName']); ?></a>
                <span class="dot">·</span>
                <a class="timestamp" href="tweet.php?id=<?php echo h($tweetId); ?>"><?php echo h(time_ago($display['created'])); ?></a>
            </div>

            <?php if (!empty($display['replyToAuthor'])): ?>
                <div class="reply-context">Replying to <a href="profile.php?id=<?php echo h((string) $display['replyToAuthorId']); ?>">@<?php echo h($display['replyToAuthor']); ?></a></div>
            <?php endif; ?>

            <a class="tweet-link" href="tweet.php?id=<?php echo h($tweetId); ?>">
                <p class="tweet-text"><?php echo nl2br(h($display['body'])); ?></p>
                <?php if (!empty($display['image'])): ?>
                    <img class="tweet-image" src="<?php echo h($display['image']); ?>" alt="">
                <?php endif; ?>
            </a>

            <div class="tweet-actions">
                <button class="action reply-btn" data-id="<?php echo h($tweetId); ?>" title="Reply">
                    💬 <span><?php echo (int) ($display['repliesCount'] ?? 0); ?></span>
                </button>
                <button class="action repost-btn <?php echo $reposted ? 'active' : ''; ?>" data-id="<?php echo h($tweetId); ?>" title="Repost">
                    🔁 <span class="repost-count"><?php echo (int) ($display['retweetsCount'] ?? 0); ?></span>
                </button>
                <button class="action like-btn <?php echo $liked ? 'active' : ''; ?>" data-id="<?php echo h($tweetId); ?>" title="Like">
                    <span class="like-icon"><?php echo $liked ? '❤️' : '🤍'; ?></span> <span class="like-count"><?php echo (int) ($display['likesCount'] ?? 0); ?></span>
                </button>
                <?php if ($isOwner): ?>
                    <button class="action delete-btn" data-id="<?php echo h($tweetId); ?>" title="Delete">🗑️</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>
