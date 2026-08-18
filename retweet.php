<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$me = current_user();
if (!$me) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

csrf_verify();
rate_limit('retweet', 60, 60);

$tweetId = to_object_id($_POST['id'] ?? '');
if (!$tweetId) {
    echo json_encode(['ok' => false, 'error' => 'Invalid tweet']);
    exit;
}

$original = $db->tweets->findOne(['_id' => $tweetId]);
if (!$original) {
    echo json_encode(['ok' => false, 'error' => 'Tweet not found']);
    exit;
}

$existing = $db->tweets->findOne(['retweetOf' => $tweetId, 'authorId' => $me['_id']]);

if ($existing) {
    $db->tweets->deleteOne(['_id' => $existing['_id']]);
    $db->tweets->updateOne(['_id' => $tweetId], ['$inc' => ['retweetsCount' => -1]]);
    $reposted = false;
} else {
    $db->tweets->insertOne([
        'authorId' => $me['_id'],
        'authorName' => $me['username'],
        'authorAvatar' => $me['avatar'] ?? null,
        'body' => '',
        'image' => null,
        'retweetOf' => $tweetId,
        'created' => new MongoDB\BSON\UTCDateTime(),
        'likesCount' => 0,
        'repliesCount' => 0,
        'retweetsCount' => 0,
    ]);
    $db->tweets->updateOne(['_id' => $tweetId], ['$inc' => ['retweetsCount' => 1]]);
    create_notification($original['authorId'], $me, 'retweet', $tweetId);
    $reposted = true;
}

$fresh = $db->tweets->findOne(['_id' => $tweetId]);
echo json_encode(['ok' => true, 'reposted' => $reposted, 'count' => $fresh['retweetsCount']]);
