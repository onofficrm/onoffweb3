<?php
/**
 * Google Maps 모듈 설정
 * - _site.config.php 의 $site_config 값 + fallback
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

/**
 * 지도 설정 배열 반환 (캐시 1회)
 *
 * @return array<string, mixed>
 */
if (!function_exists('onoff_map_get_config')) {
    function onoff_map_get_config()
    {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $lat = g5site_cfg('map_default_lat', '10.3157');
        $lng = g5site_cfg('map_default_lng', '123.8854');
        $zoom = (int) g5site_cfg('map_default_zoom', '13');
        if ($zoom < 1 || $zoom > 21) {
            $zoom = 13;
        }

        $radius = (float) g5site_cfg('map_default_radius_km', '5');
        if ($radius <= 0) {
            $radius = 5;
        }

        $unit = g5site_cfg('map_unit', 'km');
        if ($unit !== 'km' && $unit !== 'mi') {
            $unit = 'km';
        }

        $cached = array(
            'api_key'                 => g5site_cfg('google_maps_api_key', ''),
            'default_lat'             => is_numeric($lat) ? (float) $lat : 10.3157,
            'default_lng'             => is_numeric($lng) ? (float) $lng : 123.8854,
            'default_zoom'            => $zoom,
            'use_current_location'    => g5site_cfg_bool('map_use_current_location', true),
            'default_radius_km'       => $radius,
            'unit'                    => $unit,
            'placeholder_title'       => g5site_cfg('map_placeholder_title', 'Google Maps API 키가 설정되지 않았습니다.'),
            'placeholder_desc'        => g5site_cfg('map_placeholder_desc', '_site.config.php에서 google_maps_api_key 값을 입력하면 지도가 표시됩니다.'),
            'data_url_default'        => (defined('G5_URL') ? G5_URL : '') . '/data/map-locations.sample.json',
        );

        return $cached;
    }
}

/**
 * API 키 설정 여부
 */
if (!function_exists('onoff_map_has_api_key')) {
    function onoff_map_has_api_key()
    {
        $cfg = onoff_map_get_config();

        return isset($cfg['api_key']) && $cfg['api_key'] !== '';
    }
}

/**
 * data-* 속성용 이스케이프
 *
 * @param string|int|float $value
 * @return string
 */
if (!function_exists('onoff_map_esc_attr')) {
    function onoff_map_esc_attr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * JSON 샘플 파일에서 장소 목록 로드 (PHP용, 선택)
 *
 * @param string $path 절대 경로
 * @return array<int, array<string, mixed>>
 */
if (!function_exists('onoff_map_load_locations_json')) {
    function onoff_map_load_locations_json($path = '')
    {
        if ($path === '') {
            $path = G5_PATH . '/data/map-locations.sample.json';
        }

        if (!is_file($path) || !is_readable($path)) {
            return array();
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return array();
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return array();
        }

        return $data;
    }
}
