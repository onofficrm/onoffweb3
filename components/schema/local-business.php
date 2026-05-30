<?php
/**
 * LocalBusiness JSON-LD
 * - $local_business_type — 스키마 타입 (기본 LocalBusiness)
 * - $local_business_opening_hours — 문자열 배열 (선택)
 * - $local_business_price_range — 예: $$ (선택)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/_helpers.php';

g5b_schema_load_config();

$site_url = g5b_schema_site_url();
$site_name = g5b_schema_cfg('site_name', '');
$company_name = g5b_schema_cfg('company_name', $site_name);

if ($company_name === '' && $site_name !== '') {
    $company_name = $site_name;
}
if ($company_name === '') {
    return;
}

$phone = g5b_schema_cfg('phone', '');
$address = g5b_schema_cfg('address', '');
$has_contact = ($phone !== '' || ($address !== '' && $address !== '주소를 입력하세요'));

if (!$has_contact && $site_url === '') {
    return;
}

$type = 'LocalBusiness';
if (!empty($local_business_type)) {
    $type = g5b_schema_sanitize_type($local_business_type, 'LocalBusiness');
}

$schema = array(
    '@context' => 'https://schema.org',
    '@type'    => $type,
    'name'     => $company_name,
);

if ($site_url !== '') {
    $schema['@id'] = $site_url . '#localbusiness';
    $schema['url'] = $site_url;
}

$logo_url = g5b_schema_cfg_url('logo_path', '');
if ($logo_url !== '') {
    $schema['image'] = $logo_url;
}

if ($phone !== '') {
    $schema['telephone'] = $phone;
}

$email = g5b_schema_cfg('email', '');
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $schema['email'] = $email;
}

if ($address !== '' && $address !== '주소를 입력하세요') {
    $schema['address'] = array(
        '@type'         => 'PostalAddress',
        'streetAddress' => $address,
    );
}

$lat = g5b_schema_cfg('kakao_map_lat', '');
$lng = g5b_schema_cfg('kakao_map_lng', '');
if ($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)) {
    $schema['geo'] = array(
        '@type'     => 'GeoCoordinates',
        'latitude'  => (float) $lat,
        'longitude' => (float) $lng,
    );
}

if (!empty($local_business_opening_hours) && is_array($local_business_opening_hours)) {
    $hours = array();
    foreach ($local_business_opening_hours as $line) {
        $line = g5b_schema_clean_text($line);
        if ($line !== '') {
            $hours[] = $line;
        }
    }
    if (!empty($hours)) {
        $schema['openingHours'] = $hours;
    }
}

if (!empty($local_business_price_range)) {
    $schema['priceRange'] = g5b_schema_clean_text($local_business_price_range);
}

g5b_schema_print_jsonld($schema);
