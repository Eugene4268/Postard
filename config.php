<?php
/**
 * Central app configuration. All secrets come from environment variables
 * so nothing sensitive is hard-coded in source control.
 */

// Load a .env file if present (simple parser, no composer dep needed)
function load_env($path)
{
    if (!file_exists($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (getenv($key) === false) {
            putenv("$key=$value");
        }
    }
}

load_env(__DIR__ . '/.env');

function env($key, $default = null)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

define('MONGODB_URI', env('MONGODB_URI', 'mongodb://localhost:27017'));
define('MONGODB_DB', env('MONGODB_DB', 'postarddb'));
define('APP_NAME', env('APP_NAME', 'Postard'));
define('APP_URL', env('APP_URL', 'http://localhost:8080'));
define('MAX_TWEET_LENGTH', 280);
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Session must be started before anything else touches $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    $secure = (env('APP_ENV', 'production') === 'production');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Baseline security headers on every request
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline'; script-src 'self'");

require_once __DIR__ . '/vendor/autoload.php';

try {
    $mongoClient = new MongoDB\Client(MONGODB_URI);
    $db = $mongoClient->selectDatabase(MONGODB_DB);
} catch (Exception $e) {
    http_response_code(500);
    die('Database connection failed. Please try again later.');
}

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
