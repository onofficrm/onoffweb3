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
$phone = cebu24_normalize_phone(isset($body['phone']) ? $body['phone'] : '');
$pin = isset($body['pin']) ? preg_replace('/\D+/', '', (string) $body['pin']) : '';
$pin = substr($pin, 0, 4);

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
if (!cebu24_rate_limit_ok('ip:' . $ip, 40, 600)) {
    cebu24_api_json(false, array('error' => 'rate_limited'), 429);
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
