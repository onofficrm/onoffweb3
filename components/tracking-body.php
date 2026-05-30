<?php
/**
 * body 시작 직후 GTM noscript — gtm_id 있을 때만 출력
 * head.php에서 head.sub.php 직후 include 권장
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg') && is_file(G5_PATH . '/_site.config.php')) {
    include_once G5_PATH . '/_site.config.php';
}

$gtm_id = function_exists('g5site_cfg') ? trim(g5site_cfg('gtm_id', '')) : '';
$gtm_id = preg_replace('/[^a-zA-Z0-9\-_]/', '', $gtm_id);

if ($gtm_id === '') {
    return;
}

echo '<!-- Google Tag Manager (noscript) -->' . "\n";
echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . htmlspecialchars($gtm_id, ENT_QUOTES, 'UTF-8') . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
