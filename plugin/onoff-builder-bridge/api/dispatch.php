<?php
/**
 * CEBU 24 dispatch API — save / lookup by phone + 4-digit PIN
 * POST JSON: { action: "save"|"lookup", phone, pin, item? }
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

include_once dirname(__FILE__) . '/../_common.php';
include_once dirname(__FILE__) . '/../bootstrap.php';
include_once dirname(__FILE__) . '/../lib/dispatch_store.php';
include_once dirname(__FILE__) . '/../lib/admin_store.php';

function cebu24_api_json($ok, $payload = array(), $http = 200)
{
    http_response_code($http);
    echo json_encode(array_merge(array('ok' => $ok), $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cebu24_api_json(false, array('error' => 'method_not_allowed'), 405);
}

$raw = file_get_contents('php://input');
$body = $raw ? json_decode($raw, true) : null;
if (!is_array($body)) {
    cebu24_api_json(false, array('error' => 'invalid_json'), 400);
}

$action = isset($body['action']) ? (string) $body['action'] : '';

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
if (!cebu24_rate_limit_ok('ip:' . $ip, 40, 600)) {
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
    || $action === 'admin_list' || $action === 'admin_status') {
    $admin = cebu24_admin_from_token($token);
    if (!$admin) {
        cebu24_api_json(false, array('error' => 'admin_session_required'), 401);
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

if (strlen($phone) < 10 || strlen($phone) > 13) {
    cebu24_api_json(false, array('error' => 'invalid_phone'), 400);
}
if (strlen($pin) !== 4) {
    cebu24_api_json(false, array('error' => 'invalid_pin'), 400);
}

if (!cebu24_rate_limit_ok('phone:' . $phone, 25, 600)) {
    cebu24_api_json(false, array('error' => 'rate_limited'), 429);
}

if ($action === 'save') {
    $item = isset($body['item']) && is_array($body['item']) ? cebu24_sanitize_dispatch_item($body['item']) : null;
    if (!$item || empty($item['id'])) {
        cebu24_api_json(false, array('error' => 'invalid_item'), 400);
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

    $items = isset($bucket['items']) && is_array($bucket['items']) ? $bucket['items'] : array();
    $next = array($item);
    foreach ($items as $old) {
        if (!is_array($old) || !isset($old['id']) || $old['id'] === $item['id']) {
            continue;
        }
        $next[] = $old;
    }
    $bucket['items'] = array_slice($next, 0, 30);
    $bucket['updated_at'] = date('c');

    if (!cebu24_save_phone_bucket($phone, $bucket)) {
        cebu24_api_json(false, array('error' => 'save_failed'), 500);
    }
    cebu24_api_json(true, array('id' => $item['id'], 'count' => count($bucket['items'])));
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
    cebu24_api_json(true, array('items' => $items, 'phone' => $phone));
}

cebu24_api_json(false, array('error' => 'unknown_action'), 400);
