<?php
/**
 * CEBU 24 dispatch API — save / lookup by phone + 4-digit PIN
 * POST JSON: { action: "save"|"lookup", phone, pin, item? }
 */
@ini_set('display_errors', '0');
@ini_set('html_errors', '0');
ob_start();

include_once dirname(__FILE__) . '/../_common.php';
include_once dirname(__FILE__) . '/../bootstrap.php';
include_once dirname(__FILE__) . '/../lib/dispatch_store.php';
include_once dirname(__FILE__) . '/../lib/admin_store.php';
include_once dirname(__FILE__) . '/../lib/apns.php';

set_error_handler(function ($severity, $message, $file, $line) {
    error_log('CEBU24 dispatch PHP error: ' . $message . ' in ' . $file . ':' . $line);
    return true;
});

function cebu24_api_json($ok, $payload = array(), $http = 200)
{
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    $json = json_encode(array_merge(array('ok' => $ok), $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        $json = '{"ok":false,"error":"json_encode_failed"}';
        $http = 500;
    }
    if (!headers_sent()) {
        header_remove('Content-Type');
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
        http_response_code($http);
    } else {
        error_log('CEBU24 dispatch headers already sent before JSON response');
    }
    echo $json;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cebu24_api_json(false, array('error' => 'method_not_allowed'), 405);
}

$content_type = isset($_SERVER['CONTENT_TYPE']) ? strtolower((string) $_SERVER['CONTENT_TYPE']) : '';
$is_multipart = strpos($content_type, 'multipart/form-data') !== false;
if ($is_multipart) {
    $body = $_POST;
    if (empty($body) && !empty($_SERVER['CONTENT_LENGTH'])) {
        cebu24_api_json(false, array('error' => 'upload_request_too_large'), 413);
    }
} else {
    $raw = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : null;
    if (!is_array($body)) {
        cebu24_api_json(false, array('error' => 'invalid_json'), 400);
    }
}

$action = isset($body['action']) ? (string) $body['action'] : '';

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
// Many customers share one carrier IP, and admin screens poll the list every 30 seconds,
// so read traffic must never consume the budget that customer submissions depend on.
$rate_bucket = 'customer';
$rate_max = 120;
if ($action === 'list') {
    $rate_bucket = 'list';
    $rate_max = 600;
} elseif (strpos($action, 'admin_') === 0 || strpos($action, 'apns_') === 0) {
    $rate_bucket = 'admin';
    $rate_max = 600;
}
if (!cebu24_rate_limit_ok('ip-' . $rate_bucket . ':' . $ip, $rate_max, 600)) {
    cebu24_api_json(false, array('error' => 'rate_limited'), 429);
}

if ($action === 'admin_login') {
    if (!cebu24_rate_limit_ok('admin-login:' . $ip, 10, 600)) {
        cebu24_api_json(false, array('error' => 'rate_limited'), 429);
    }
    $admin_id = isset($body['adminId']) ? (string) $body['adminId'] : '';
    $password = isset($body['password']) ? (string) $body['password'] : '';
    $session = cebu24_admin_login($admin_id, $password);
    if (!$session) {
        cebu24_api_json(false, array('error' => 'invalid_admin_credentials'), 403);
    }
    cebu24_api_json(true, $session);
}

$token = isset($body['token']) ? (string) $body['token'] : '';

if ($action === 'admin_me' || $action === 'admin_logout' || $action === 'admin_register'
    || $action === 'admin_accounts' || $action === 'admin_delete'
    || $action === 'admin_change_password' || $action === 'admin_reset_password'
    || $action === 'admin_list' || $action === 'admin_status'
    || $action === 'apns_register' || $action === 'apns_unregister') {
    $admin = cebu24_admin_from_token($token);
    if (!$admin) {
        cebu24_api_json(false, array('error' => 'admin_session_required'), 401);
    }
    if ($action === 'apns_register' || $action === 'apns_unregister') {
        $device_token = isset($body['deviceToken']) ? (string) $body['deviceToken'] : '';
        if ($action === 'apns_unregister') {
            if (!cebu24_apns_unregister_device($device_token)) {
                cebu24_api_json(false, array('error' => 'invalid_device_token'), 400);
            }
            cebu24_api_json(true);
        }
        $environment = isset($body['environment']) ? (string) $body['environment'] : 'production';
        $bundle_id = isset($body['bundleId']) ? (string) $body['bundleId'] : '';
        if (!cebu24_apns_register_device($device_token, $environment, $bundle_id, $admin['id'])) {
            cebu24_api_json(false, array('error' => 'invalid_device'), 400);
        }
        cebu24_api_json(true);
    }
    if ($action === 'admin_me') {
        cebu24_api_json(true, array('admin' => $admin));
    }
    if ($action === 'admin_logout') {
        cebu24_admin_logout($token);
        cebu24_api_json(true);
    }
    if ($action === 'admin_register') {
        $new_id = isset($body['adminId']) ? (string) $body['adminId'] : '';
        $new_password = isset($body['password']) ? (string) $body['password'] : '';
        $new_name = isset($body['name']) ? (string) $body['name'] : '';
        $created = cebu24_admin_create($new_id, $new_password, $new_name, $admin['id']);
        if (empty($created['ok'])) {
            cebu24_api_json(false, array('error' => $created['error']), 400);
        }
        cebu24_api_json(true, array(
            'admin' => $created['admin'],
            'bootstrapReplaced' => !empty($created['bootstrapReplaced']),
        ));
    }
    if ($action === 'admin_accounts') {
        cebu24_api_json(true, array('items' => cebu24_admin_list_accounts()));
    }
    if ($action === 'admin_delete') {
        $target_id = isset($body['adminId']) ? (string) $body['adminId'] : '';
        $deleted = cebu24_admin_delete($target_id, $admin['id']);
        if (empty($deleted['ok'])) {
            cebu24_api_json(false, array('error' => $deleted['error']), 400);
        }
        cebu24_api_json(true);
    }
    if ($action === 'admin_change_password') {
        $current_password = isset($body['currentPassword']) ? (string) $body['currentPassword'] : '';
        $new_password = isset($body['newPassword']) ? (string) $body['newPassword'] : '';
        $changed = cebu24_admin_change_password($admin['id'], $current_password, $new_password);
        if (empty($changed['ok'])) {
            cebu24_api_json(false, array('error' => $changed['error']), 400);
        }
        cebu24_api_json(true);
    }
    if ($action === 'admin_reset_password') {
        $target_id = isset($body['adminId']) ? (string) $body['adminId'] : '';
        $new_password = isset($body['newPassword']) ? (string) $body['newPassword'] : '';
        $reset = cebu24_admin_reset_password($target_id, $new_password, $admin['id']);
        if (empty($reset['ok'])) {
            cebu24_api_json(false, array('error' => $reset['error']), 400);
        }
        cebu24_api_json(true);
    }
    if ($action === 'admin_list') {
        cebu24_api_json(true, array('items' => cebu24_admin_list_dispatches(), 'admin' => $admin));
    }
    $id = isset($body['id']) ? (string) $body['id'] : '';
    $status = isset($body['status']) ? (string) $body['status'] : '';
    if (!cebu24_admin_set_status($id, $status, $admin['id'])) {
        cebu24_api_json(false, array('error' => 'not_found'), 404);
    }
    cebu24_api_json(true, array('id' => $id, 'status' => $status, 'handledBy' => $admin['id']));
}

$phone = cebu24_normalize_phone(isset($body['phone']) ? $body['phone'] : '');
$pin = isset($body['pin']) ? preg_replace('/\D+/', '', (string) $body['pin']) : '';
$pin = substr($pin, 0, 4);

if ($action === 'list') {
    $mark_phone = (strlen($phone) >= 10 && strlen($pin) === 4) ? $phone : '';
    $mark_pin = $mark_phone !== '' ? $pin : '';
    $items = cebu24_list_public_dispatches($mark_phone, $mark_pin);
    cebu24_api_json(true, array('items' => $items));
}

if (!cebu24_phone_is_callable($phone) && !cebu24_phone_is_onsite_code($phone)) {
    cebu24_api_json(false, array('error' => 'invalid_phone'), 400);
}
if (strlen($pin) !== 4) {
    cebu24_api_json(false, array('error' => 'invalid_pin'), 400);
}

if (!cebu24_rate_limit_ok('phone:' . $phone, 40, 600)) {
    cebu24_api_json(false, array('error' => 'rate_limited'), 429);
}

if ($action === 'save' || $action === 'save_with_photos') {
    $raw_item = isset($body['item']) ? $body['item'] : null;
    // Gnuboard applies addslashes() to every $_POST value. Multipart item JSON
    // therefore arrives escaped ({\"id\":...}) and json_decode() fails unless
    // we stripslashes first. JSON request bodies use php://input and are fine.
    if (is_string($raw_item)) {
        $decoded = json_decode($raw_item, true);
        if (!is_array($decoded)) {
            $decoded = json_decode(stripslashes($raw_item), true);
        }
        $raw_item = $decoded;
    }
    $item = is_array($raw_item) ? cebu24_sanitize_dispatch_item($raw_item) : null;
    if (!$item || empty($item['id'])) {
        cebu24_api_json(false, array('error' => 'invalid_item'), 400);
    }
    if (cebu24_phone_is_onsite_code($phone)) {
        $landmark = isset($item['landmark']) ? trim((string) $item['landmark']) : '';
        $plate = isset($item['plateNumber']) ? trim((string) $item['plateNumber']) : '';
        $landmark_len = function_exists('mb_strlen') ? mb_strlen($landmark) : strlen($landmark);
        if ($landmark_len < 4 || $plate === '' || $plate === '번호판 미입력') {
            cebu24_api_json(false, array('error' => 'onsite_details_required'), 400);
        }
        $item['contactChannel'] = 'onsite';
        $note = isset($item['notes']) ? (string) $item['notes'] : '';
        if (strpos($note, '현장 대기') === false) {
            $item['notes'] = trim('현장 대기. 전화 없이 위치와 번호판으로 찾아주세요. ' . $note);
        }
    }
    $item['contactPhone'] = $phone;
    $item['customerPin'] = $pin;
    if (empty($item['adminCallStatus'])) {
        $item['adminCallStatus'] = 'pending';
    }
    $item['savedAt'] = date('c');

    $bucket = cebu24_load_phone_bucket($phone);
    if ($bucket && !empty($bucket['pin_hash'])) {
        if (!password_verify($pin, $bucket['pin_hash'])) {
            cebu24_api_json(false, array('error' => 'pin_mismatch'), 403);
        }
    } else {
        $bucket = array(
            'phone' => $phone,
            'pin_hash' => password_hash($pin, PASSWORD_DEFAULT),
            'created_at' => date('c'),
            'items' => array(),
        );
    }

    // Keep existing pin_hash; set if somehow missing
    if (empty($bucket['pin_hash'])) {
        $bucket['pin_hash'] = password_hash($pin, PASSWORD_DEFAULT);
    }

    $uploaded_now = array();
    if ($action === 'save_with_photos') {
        $upload = cebu24_photo_store_uploads('photos');
        if (empty($upload['ok'])) {
            cebu24_api_json(false, array('error' => $upload['error']), 400);
        }
        $uploaded_now = isset($upload['photos']) && is_array($upload['photos']) ? $upload['photos'] : array();
    }
    $new_photos = $uploaded_now;

    $items = isset($bucket['items']) && is_array($bucket['items']) ? $bucket['items'] : array();
    $is_new = true;
    $old_photos = array();
    foreach ($items as $old) {
        if (!is_array($old) || !isset($old['id'])) {
            continue;
        }
        if ((string) $old['id'] === (string) $item['id']) {
            $is_new = false;
            $old_photos = isset($old['photos']) && is_array($old['photos']) ? $old['photos'] : array();
            break;
        }
    }
    // Same receipt ID is updated in place. An empty retry keeps the photos already stored.
    if (!$is_new && empty($new_photos) && !empty($old_photos)) {
        $new_photos = $old_photos;
        $old_photos = array();
    }
    $item['photos'] = $new_photos;

    $next = array($item);
    foreach ($items as $old) {
        if (!is_array($old) || !isset($old['id']) || (string) $old['id'] === (string) $item['id']) {
            continue;
        }
        $next[] = $old;
    }
    $dropped_photos = array();
    foreach (array_slice($next, 30) as $dropped) {
        if (is_array($dropped) && !empty($dropped['photos']) && is_array($dropped['photos'])) {
            $dropped_photos = array_merge($dropped_photos, $dropped['photos']);
        }
    }
    $bucket['items'] = array_slice($next, 0, 30);
    $bucket['updated_at'] = date('c');

    if (!cebu24_save_phone_bucket($phone, $bucket)) {
        cebu24_photo_delete_many($uploaded_now);
        cebu24_api_json(false, array('error' => 'save_failed'), 500);
    }
    if (!empty($old_photos)) {
        cebu24_photo_delete_many(array_values(array_diff($old_photos, $new_photos)));
    }
    if (!empty($dropped_photos)) {
        cebu24_photo_delete_many($dropped_photos);
    }
    if ($is_new) {
        try {
            cebu24_apns_notify_new_dispatch($item);
        } catch (Throwable $e) {
            error_log('CEBU24 APNs notify failed: ' . $e->getMessage());
        }
    }
    $public_item = cebu24_sanitize_dispatch_item($item, true);
    cebu24_api_json(true, array(
        'id' => $item['id'],
        'count' => count($bucket['items']),
        'photos' => $new_photos,
        'item' => $public_item ? $public_item : array('id' => $item['id'], 'photos' => $new_photos),
    ));
}

if ($action === 'lookup') {
    $bucket = cebu24_load_phone_bucket($phone);
    if (!$bucket || empty($bucket['pin_hash'])) {
        cebu24_api_json(false, array('error' => 'not_found'), 404);
    }
    if (!password_verify($pin, $bucket['pin_hash'])) {
        cebu24_api_json(false, array('error' => 'pin_mismatch'), 403);
    }
    $items = isset($bucket['items']) && is_array($bucket['items']) ? $bucket['items'] : array();
    $safe_items = array();
    foreach ($items as $item) {
        $clean = cebu24_sanitize_dispatch_item($item);
        if ($clean) {
            $safe_items[] = $clean;
        }
    }
    cebu24_api_json(true, array('items' => $safe_items, 'phone' => $phone));
}

cebu24_api_json(false, array('error' => 'unknown_action'), 400);
