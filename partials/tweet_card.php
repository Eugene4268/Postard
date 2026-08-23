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
$bookmarked = $db->bookmarks->findOne(['tweetId' => $display['_id'], 'userId' => $me['_id']]) !== null;
$isOwner = (string) $display['authorId'] === (string) $me['_id'];
$isComment = $isComment ?? false;
?>
<article class="tweet<?php echo $isComment ? ' comment' : ''; ?>" data-tweet-id="<?php echo h($tweetId); ?>">
    <?php if ($isRepost): ?>
        <div class="repost-label"><i data-lucide="repeat-2"></i><a href="profile.php?id=<?php echo h((string) $tweet['authorId']); ?>"><?php echo h($tweet['authorName']); ?></a><span>reposted</span></div>
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
                    <i data-lucide="message-circle"></i><span><?php echo (int) ($display['repliesCount'] ?? 0); ?></span>
                </button>
                <button class="action repost-btn <?php echo $reposted ? 'active' : ''; ?>" data-id="<?php echo h($tweetId); ?>" title="Repost">
                    <i data-lucide="repeat-2"></i><span class="repost-count"><?php echo (int) ($display['retweetsCount'] ?? 0); ?></span>
                </button>
                <button class="action like-btn <?php echo $liked ? 'active' : ''; ?>" data-id="<?php echo h($tweetId); ?>" title="Like">
                    <i data-lucide="heart" class="like-icon"></i><span class="like-count"><?php echo (int) ($display['likesCount'] ?? 0); ?></span>
                </button>
                <button class="action bookmark-btn <?php echo $bookmarked ? 'active' : ''; ?>" data-id="<?php echo h($tweetId); ?>" title="Bookmark"><i data-lucide="bookmark"></i></button>
                <button class="action share-btn" data-id="<?php echo h($tweetId); ?>" title="Share"><i data-lucide="share"></i></button>
                <?php if ($isOwner): ?>
                    <button class="action delete-btn" data-id="<?php echo h($tweetId); ?>" title="Delete"><i data-lucide="trash-2"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>
