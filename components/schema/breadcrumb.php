<?php
/**
 * BreadcrumbList JSON-LD
 *
 * $breadcrumb_items = array(
 *   array('name' => '홈', 'url' => G5_URL),
 *   array('name' => '서비스', 'url' => G5_URL.'/page/service.php'),
 * );
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/_helpers.php';

if (empty($breadcrumb_items) || !is_array($breadcrumb_items)) {
    return;
}

$list = array();
$position = 1;

foreach ($breadcrumb_items as $item) {
    if (!is_array($item)) {
        continue;
    }

    $name = isset($item['name']) ? g5b_schema_clean_text($item['name']) : '';
    $url = isset($item['url']) ? g5b_schema_abs_url($item['url']) : '';

    if ($name === '' || $url === '') {
        continue;
    }

    $list[] = array(
        '@type'    => 'ListItem',
        'position' => $position,
        'name'     => $name,
        'item'     => $url,
    );
    $position++;
}

if (count($list) < 2) {
    return;
}

$schema = array(
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => $list,
);

g5b_schema_print_jsonld($schema);
