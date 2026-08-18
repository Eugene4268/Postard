<?php
require_once __DIR__ . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('userlist.php');
}

csrf_verify();
rate_limit('follow', 60, 60);

$me = current_user();
$targetId = to_object_id($_POST['id'] ?? '');
$redirectTo = $_POST['redirect'] ?? 'userlist.php';

// Only allow redirecting back to a same-app relative path (open-redirect guard).
if (!preg_match('#^[a-zA-Z0-9_./?=&-]+$#', $redirectTo) || str_starts_with($redirectTo, '//')) {
    $redirectTo = 'userlist.php';
}

if (!$targetId || (string) $targetId === (string) $me['_id']) {
    redirect($redirectTo);
}

$target = $db->users->findOne(['_id' => $targetId]);
if (!$target) {
    redirect($redirectTo);
}

$existing = $db->following->findOne(['follower' => $me['_id'], 'followingId' => $targetId]);

if ($existing) {
    $db->following->deleteOne(['_id' => $existing['_id']]);
} else {
    $db->following->insertOne([
        'follower' => $me['_id'],
        'followingId' => $targetId,
        'created' => new MongoDB\BSON\UTCDateTime(),
    ]);
    create_notification($targetId, $me, 'follow');
}

redirect($redirectTo);
