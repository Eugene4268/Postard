<?php
require_once __DIR__ . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('messages.php');
}

csrf_verify();
rate_limit('send_message', 60, 60);

$me = current_user();
$recipientId = to_object_id($_POST['recipient_id'] ?? '');
$body = trim($_POST['body'] ?? '');

if (!$recipientId || $body === '' || mb_strlen($body) > 1000) {
    redirect('messages.php');
}

$recipient = $db->users->findOne(['_id' => $recipientId]);
if (!$recipient || (string) $recipient['_id'] === (string) $me['_id']) {
    redirect('messages.php');
}

$db->messages->insertOne([
    'senderId' => $me['_id'],
    'recipientId' => $recipientId,
    'body' => $body,
    'read' => false,
    'created' => new MongoDB\BSON\UTCDateTime(),
]);

create_notification($recipientId, $me, 'message');

redirect('conversation.php?id=' . (string) $recipientId);
