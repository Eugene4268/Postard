<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$me = current_user();
if (!$me) {
    http_response_code(401);
    echo json_encode(['count' => 0]);
    exit;
}

echo json_encode(['count' => unread_notification_count($me['_id'])]);
