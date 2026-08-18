<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$me = current_user();
if (!$me) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

csrf_verify();

$tweetId = to_object_id($_POST['id'] ?? '');
if (!$tweetId) {
    echo json_encode(['ok' => false]);
    exit;
}

$tweet = $db->tweets->findOne(['_id' => $tweetId]);

// Only the author may delete their own post.
if (!$tweet || (string) $tweet['authorId'] !== (string) $me['_id']) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

$db->tweets->deleteOne(['_id' => $tweetId]);
$db->likes->deleteMany(['tweetId' => $tweetId]);
$db->tweets->deleteMany(['retweetOf' => $tweetId]); // remove reposts of this tweet too

if (!empty($tweet['image'])) {
    $path = __DIR__ . '/' . $tweet['image'];
    if (is_file($path)) {
        @unlink($path);
    }
}

if (!empty($tweet['replyTo'])) {
    $db->tweets->updateOne(['_id' => $tweet['replyTo']], ['$inc' => ['repliesCount' => -1]]);
}

echo json_encode(['ok' => true]);
