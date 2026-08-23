<?php
/**
 * Run this once after deploying (php db_setup.php) to create indexes.
 * Safe to run multiple times - createIndex is idempotent.
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

echo "Creating indexes on " . MONGODB_DB . "...\n";

$db->users->createIndex(['username' => 1], ['unique' => true]);
$db->users->createIndex(['email' => 1], ['unique' => true]);

$db->tweets->createIndex(['authorId' => 1, 'created' => -1]);
$db->tweets->createIndex(['created' => -1]);
$db->tweets->createIndex(['replyTo' => 1]);
$db->tweets->createIndex(['retweetOf' => 1]);
$db->tweets->createIndex(['body' => 'text']);

$db->likes->createIndex(['tweetId' => 1, 'userId' => 1], ['unique' => true]);
$db->bookmarks->createIndex(['tweetId' => 1, 'userId' => 1], ['unique' => true]);

$db->following->createIndex(['follower' => 1, 'followingId' => 1], ['unique' => true]);
$db->following->createIndex(['followingId' => 1]);

$db->notifications->createIndex(['userId' => 1, 'created' => -1]);

$db->messages->createIndex(['senderId' => 1, 'recipientId' => 1, 'created' => 1]);
$db->messages->createIndex(['recipientId' => 1, 'read' => 1]);

echo "Done.\n";
