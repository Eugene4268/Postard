<?php
/**
 * Authentication / authorization helpers. Relies on config.php having
 * already started the session and connected to $db.
 */

/** Get the logged-in user's full document, or null. Cached per-request. */
function current_user()
{
    static $cached = null;
    static $loaded = false;
    global $db;

    if ($loaded) {
        return $cached;
    }
    $loaded = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $id = to_object_id($_SESSION['user_id']);
    if (!$id) {
        return null;
    }

    $cached = $db->users->findOne(['_id' => $id]);
    return $cached;
}

/** Require the visitor to be logged in, or redirect to login. */
function require_login()
{
    if (!current_user()) {
        redirect('index.php');
    }
}

/** Log a user in: regenerate session id (prevents session fixation) and store user id. */
function log_in_user($userId)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (string) $userId;
}

/** Create a notification for $toUserId, unless it's the same as $fromUserId. */
function create_notification($toUserId, $fromUser, $type, $tweetId = null)
{
    global $db;

    $toUserId = to_object_id($toUserId);
    if (!$toUserId || (string) $toUserId === (string) $fromUser['_id']) {
        return; // don't notify yourself
    }

    $recipient = $db->users->findOne(['_id' => $toUserId], ['projection' => ['notificationPreferences' => 1]]);
    $preferences = (array) ($recipient['notificationPreferences'] ?? []);
    if (!empty($preferences['pauseAll'])) {
        return;
    }
    $preferenceKey = ['like' => 'someoneLikes', 'follow' => 'someoneFollows', 'reply' => 'someoneComments', 'retweet' => 'someoneShares', 'message' => 'newMessages'][$type] ?? null;
    if ($preferenceKey !== null && array_key_exists($preferenceKey, $preferences) && !$preferences[$preferenceKey]) {
        return;
    }
    if (!empty($preferences['quietMode'])) {
        $start = (int) ($preferences['quietStart'] ?? 22);
        $end = (int) ($preferences['quietEnd'] ?? 7);
        $hour = (int) date('G');
        $quiet = $start < $end ? ($hour >= $start && $hour < $end) : ($hour >= $start || $hour < $end);
        if ($quiet) return;
    }

    $db->notifications->insertOne([
        'userId' => $toUserId,
        'fromUserId' => $fromUser['_id'],
        'fromUsername' => $fromUser['username'],
        'fromAvatar' => $fromUser['avatar'] ?? null,
        'type' => $type, // 'like' | 'follow' | 'reply' | 'retweet' | 'message'
        'tweetId' => $tweetId ? to_object_id($tweetId) : null,
        'read' => false,
        'created' => new MongoDB\BSON\UTCDateTime(),
    ]);
}

/** Count unread notifications for the badge in the nav bar. */
function unread_notification_count($userId)
{
    global $db;
    return $db->notifications->countDocuments(['userId' => to_object_id($userId), 'read' => false]);
}
