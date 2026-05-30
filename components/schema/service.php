<?php
/**
 * Service JSON-LD (서비스 소개 페이지)
 *
 * 선택 변수:
 * - $service_name, $service_description, $service_area
 * - $service_provider (Organization 배열 또는 문자열 이름)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/_helpers.php';

g5b_schema_load_config();

$name = !empty($service_name) ? g5b_schema_clean_text($service_name) : '';
$description = !empty($service_description) ? g5b_schema_clean_text($service_description) : '';
$area = !empty($service_area) ? g5b_schema_clean_text($service_area) : '';

if ($name === '') {
    $name = g5b_schema_cfg('site_name', '');
}
if ($description === '') {
    $description = g5b_schema_cfg('site_desc', '');
    if ($description === '') {
        $description = g5b_schema_cfg('seo_description', '');
    }
}

if ($name === '') {
    return;
}

$site_url = g5b_schema_site_url();
$provider_name = g5b_schema_cfg('company_name', g5b_schema_cfg('site_name', ''));

$provider = array(
    '@type' => 'Organization',
    'name'  => $provider_name !== '' ? $provider_name : $name,
);

if ($site_url !== '') {
    $provider['url'] = $site_url;
}

$logo = g5b_schema_cfg_url('logo_path', '');
if ($logo !== '') {
    $provider['logo'] = $logo;
}

if (!empty($service_provider)) {
    if (is_array($service_provider)) {
        $custom = $service_provider;
        if (empty($custom['@type'])) {
            $custom['@type'] = 'Organization';
        }
        if (empty($custom['name']) && $provider_name !== '') {
            $custom['name'] = $provider_name;
        }
        $provider = $custom;
    } else {
        $custom_name = g5b_schema_clean_text($service_provider);
        if ($custom_name !== '') {
            $provider['name'] = $custom_name;
        }
    }
}

$schema = array(
    '@context'    => 'https://schema.org',
    '@type'       => 'Service',
    'name'        => $name,
    'provider'    => $provider,
);

if ($description !== '') {
    $schema['description'] = $description;
}

if ($area !== '') {
    $schema['areaServed'] = array(
        '@type' => 'Place',
        'name'  => $area,
    );
} elseif (g5b_schema_cfg('address', '') !== '' && g5b_schema_cfg('address', '') !== '주소를 입력하세요') {
    $schema['areaServed'] = g5b_schema_cfg('address', '');
}

if ($site_url !== '') {
    $schema['url'] = $site_url;
}

g5b_schema_print_jsonld($schema);
