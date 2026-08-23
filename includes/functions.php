<?php
/**
 * Small, dependency-free helper functions used across the app.
 */

/** Escape output to prevent XSS. Use this around EVERY piece of user data echoed into HTML. */
function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Redirect and stop execution. */
function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

/** Generate (or reuse) a CSRF token for this session. */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** HTML snippet for a hidden CSRF field, for use inside <form> blocks. */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

/** Verify a submitted CSRF token, dies with 403 on failure. */
function csrf_verify()
{
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid or expired security token. Please refresh and try again.');
    }
}

/** Human friendly relative time, e.g. "5m", "3h", "2d". */
function time_ago($datetime)
{
    if ($datetime instanceof MongoDB\BSON\UTCDateTime) {
        // Use the BSON epoch directly so the server timezone cannot shift the result.
        $timestamp = $datetime->toDateTime()->getTimestamp();
    } elseif ($datetime instanceof DateTimeInterface) {
        $timestamp = $datetime->getTimestamp();
    } elseif (is_numeric($datetime)) {
        $timestamp = (int) $datetime;
        if ($timestamp > 100000000000) {
            $timestamp = (int) floor($timestamp / 1000);
        }
    } else {
        $timestamp = strtotime((string) $datetime);
    }
    if ($timestamp === false) return '';
    $diff = time() - $timestamp;

    if ($diff < 0) $diff = 0;
    if ($diff < 60) return $diff . 's';
    if ($diff < 3600) return floor($diff / 60) . 'm';
    if ($diff < 86400) return floor($diff / 3600) . 'h';
    if ($diff < 604800) return floor($diff / 86400) . 'd';

    return date('M j', $timestamp) . (date('Y', $timestamp) != date('Y') ? ', ' . date('Y', $timestamp) : '');
}

/** Convert a value to a MongoDB ObjectId, or return null if invalid. Prevents NoSQL injection via malformed IDs. */
function to_object_id($value)
{
    if ($value instanceof MongoDB\BSON\ObjectId) {
        return $value;
    }
    if (!is_string($value) || !preg_match('/^[a-f0-9]{24}$/i', $value)) {
        return null;
    }
    try {
        return new MongoDB\BSON\ObjectId($value);
    } catch (Exception $e) {
        return null;
    }
}

/** Basic username validation: 3-20 chars, letters/numbers/underscore. */
function valid_username($username)
{
    return is_string($username) && preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/** Basic password strength check. */
function valid_password($password)
{
    return is_string($password) && strlen($password) >= 8 && strlen($password) <= 200;
}

/**
 * Handle a single uploaded image file safely.
 * Returns the stored relative path (e.g. "uploads/tweets/xxxx.jpg") or null if no/invalid file.
 * Dies with an error message if a file was submitted but is invalid.
 */
function handle_image_upload($fieldName, $subdir)
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Upload failed. Please try again.');
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        die('Image is too large (max 5MB).');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        die('Unsupported image type. Please upload a JPEG, PNG, GIF, or WebP.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => 'bin',
    };

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destDir = __DIR__ . '/../uploads/' . $subdir . '/';
    $destPath = $destDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        die('Could not save upload.');
    }

    return 'uploads/' . $subdir . '/' . $filename;
}

/** Simple per-action rate limiter using the session (best-effort, not distributed-safe). */
function rate_limit($key, $maxAttempts, $windowSeconds)
{
    $now = time();
    $bucket = $_SESSION['_rl_' . $key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];

    if ($now > $bucket['reset']) {
        $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
    }

    $bucket['count']++;
    $_SESSION['_rl_' . $key] = $bucket;

    if ($bucket['count'] > $maxAttempts) {
        http_response_code(429);
        die('Too many attempts. Please wait a moment and try again.');
    }
}
