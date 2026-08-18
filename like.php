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
rate_limit('like', 120, 60);

$tweetId = to_object_id($_POST['id'] ?? '');
if (!$tweetId) {
    echo json_encode(['ok' => false, 'error' => 'Invalid tweet']);
    exit;
}

$tweet = $db->tweets->findOne(['_id' => $tweetId]);
if (!$tweet) {
    echo json_encode(['ok' => false, 'error' => 'Tweet not found']);
    exit;
}

$existing = $db->likes->findOne(['tweetId' => $tweetId, 'userId' => $me['_id']]);

if ($existing) {
    $db->likes->deleteOne(['_id' => $existing['_id']]);
    $db->tweets->updateOne(['_id' => $tweetId], ['$inc' => ['likesCount' => -1]]);
    $liked = false;
} else {
    $db->likes->insertOne([
        'tweetId' => $tweetId,
        'userId' => $me['_id'],
        'created' => new MongoDB\BSON\UTCDateTime(),
    ]);
    $db->tweets->updateOne(['_id' => $tweetId], ['$inc' => ['likesCount' => 1]]);
    create_notification($tweet['authorId'], $me, 'like', $tweetId);
    $liked = true;
}

$fresh = $db->tweets->findOne(['_id' => $tweetId]);
echo json_encode(['ok' => true, 'liked' => $liked, 'count' => $fresh['likesCount']]);
