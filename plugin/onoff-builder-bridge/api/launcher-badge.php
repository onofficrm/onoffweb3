<?php
/**
 * Count for the Android launcher notification. GET only, no personal data.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

include_once dirname(__FILE__) . '/../_common.php';
include_once dirname(__FILE__) . '/../bootstrap.php';
include_once dirname(__FILE__) . '/../lib/dispatch_store.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('count' => 0));
    exit;
}

echo json_encode(
    array('count' => cebu24_launcher_badge_count()),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
