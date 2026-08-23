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
rate_limit('bookmark', 120, 60);

$tweetId = to_object_id($_POST['id'] ?? '');
if (!$tweetId || !$db->tweets->findOne(['_id' => $tweetId])) {
    echo json_encode(['ok' => false, 'error' => 'Tweet not found']);
    exit;
}

$existing = $db->bookmarks->findOne(['tweetId' => $tweetId, 'userId' => $me['_id']]);
if ($existing) {
    $db->bookmarks->deleteOne(['_id' => $existing['_id']]);
    $bookmarked = false;
} else {
    $db->bookmarks->insertOne([
        'tweetId' => $tweetId,
        'userId' => $me['_id'],
        'created' => new MongoDB\BSON\UTCDateTime(),
    ]);
    $bookmarked = true;
}

echo json_encode(['ok' => true, 'bookmarked' => $bookmarked]);