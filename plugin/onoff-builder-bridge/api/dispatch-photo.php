<?php
/**
 * Authenticated administrator photo stream.
 * POST JSON: { token, photoId }
 */
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');

include_once dirname(__FILE__) . '/../_common.php';
include_once dirname(__FILE__) . '/../bootstrap.php';
include_once dirname(__FILE__) . '/../lib/dispatch_store.php';
include_once dirname(__FILE__) . '/../lib/admin_store.php';

function cebu24_photo_error($error, $http)
{
    http_response_code($http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => false, 'error' => $error), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cebu24_photo_error('method_not_allowed', 405);
}

$raw = file_get_contents('php://input');
$body = $raw ? json_decode($raw, true) : null;
if (!is_array($body)) {
    cebu24_photo_error('invalid_json', 400);
}

$token = isset($body['token']) ? (string) $body['token'] : '';
$admin = cebu24_admin_from_token($token, false);
if (!$admin) {
    cebu24_photo_error('admin_session_required', 401);
}

$photo_id = isset($body['photoId']) ? basename((string) $body['photoId']) : '';
$path = cebu24_photo_path($photo_id);
if (!$path || !is_file($path)) {
    cebu24_photo_error('photo_not_found', 404);
}

$detected = cebu24_photo_mime($path, $photo_id);
if (!$detected) {
    cebu24_photo_error('invalid_photo', 415);
}

header('Content-Type: ' . $detected['mime']);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="dispatch-photo.' . $detected['extension'] . '"');
readfile($path);
exit;
