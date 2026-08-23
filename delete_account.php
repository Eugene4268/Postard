<?php
require_once __DIR__ . '/config.php';
require_login();
csrf_verify();

$me = current_user();
$userId = $me['_id'];

$db->tweets->deleteMany(['authorId' => $userId]);
$db->following->deleteMany(['$or' => [['follower' => $userId], ['followingId' => $userId]]]);
$db->likes->deleteMany(['userId' => $userId]);
$db->bookmarks->deleteMany(['userId' => $userId]);
$db->notifications->deleteMany(['$or' => [['userId' => $userId], ['fromUserId' => $userId]]]);
$db->messages->deleteMany(['$or' => [['senderId' => $userId], ['recipientId' => $userId]]]);
$db->users->deleteOne(['_id' => $userId]);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
redirect('index.php');
