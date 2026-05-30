<?php
/**
 * Organization JSON-LD
 * - 회사/브랜드 기본 정보
 * - seo-meta.php의 @graph Organization과 별도 include용 (중복 출력 주의)
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

$schema = array(
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => $company_name,
);

if ($site_url !== '') {
    $schema['@id'] = $site_url . '#organization';
    $schema['url'] = $site_url;
}

$logo_url = g5b_schema_cfg_url('logo_path', '');
if ($logo_url !== '') {
    $schema['logo'] = $logo_url;
}

$phone = g5b_schema_cfg('phone', '');
if ($phone !== '') {
    $schema['telephone'] = $phone;
}

$email = g5b_schema_cfg('email', '');
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $schema['email'] = $email;
}

$address = g5b_schema_cfg('address', '');
if ($address !== '' && $address !== '주소를 입력하세요') {
    $schema['address'] = array(
        '@type'         => 'PostalAddress',
        'streetAddress' => $address,
    );
}

g5b_schema_print_jsonld($schema);
