<?php
require_once __DIR__ . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('home.php');
}

csrf_verify();
rate_limit('create_tweet', 30, 300); // 30 posts per 5 minutes

$me = current_user();
$body = trim($_POST['body'] ?? '');

if ($body === '' || mb_strlen($body) > MAX_TWEET_LENGTH) {
    die('Post must be between 1 and ' . MAX_TWEET_LENGTH . ' characters.');
}

$imagePath = handle_image_upload('image', 'tweets');

$replyToId = to_object_id($_POST['reply_to'] ?? null);
$replyDoc = null;
if ($replyToId) {
    $replyDoc = $db->tweets->findOne(['_id' => $replyToId]);
}

$tweet = [
    'authorId' => $me['_id'],
    'authorName' => $me['username'],
    'authorAvatar' => $me['avatar'] ?? null,
    'body' => $body,
    'image' => $imagePath,
    'created' => new MongoDB\BSON\UTCDateTime(),
    'likesCount' => 0,
    'repliesCount' => 0,
    'retweetsCount' => 0,
];

if ($replyDoc) {
    $tweet['replyTo'] = $replyDoc['_id'];
    $tweet['replyToAuthor'] = $replyDoc['authorName'];
    $tweet['replyToAuthorId'] = $replyDoc['authorId'];
}

$result = $db->tweets->insertOne($tweet);

if ($replyDoc) {
    $db->tweets->updateOne(['_id' => $replyDoc['_id']], ['$inc' => ['repliesCount' => 1]]);
    create_notification($replyDoc['authorId'], $me, 'reply', $result->getInsertedId());
    redirect('tweet.php?id=' . (string) $replyDoc['_id']);
}

redirect('home.php');
