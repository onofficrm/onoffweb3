<?php
/**
 * Google Map 컨테이너 (API 키 없으면 placeholder)
 *
 * 옵션 $map_module_id — 동일 페이지 여러 지도 시 id 접미사
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/map-config.php';

$map_cfg = onoff_map_get_config();
$map_has_key = onoff_map_has_api_key();

$map_module_id = isset($map_module_id) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $map_module_id) : 'main';
if ($map_module_id === '') {
    $map_module_id = 'main';
}

$map_lat = isset($map_lat) ? $map_lat : $map_cfg['default_lat'];
$map_lng = isset($map_lng) ? $map_lng : $map_cfg['default_lng'];
$map_zoom = isset($map_zoom) ? (int) $map_zoom : $map_cfg['default_zoom'];
$map_data_url = isset($map_data_url) ? $map_data_url : $map_cfg['data_url_default'];
$map_radius = isset($map_radius) ? (float) $map_radius : $map_cfg['default_radius_km'];
$map_locations_json = '';
if (!empty($map_locations) && is_array($map_locations)) {
    $map_locations_json = json_encode($map_locations, JSON_UNESCAPED_UNICODE);
    if ($map_locations_json === false) {
        $map_locations_json = '';
    }
}

$map_canvas_id = 'googleMap_' . $map_module_id;
?>

<div class="map-module"
     data-map-module-id="<?php echo onoff_map_esc_attr($map_module_id); ?>"
     data-map-lat="<?php echo onoff_map_esc_attr($map_lat); ?>"
     data-map-lng="<?php echo onoff_map_esc_attr($map_lng); ?>"
     data-map-zoom="<?php echo onoff_map_esc_attr($map_zoom); ?>"
     data-map-data-url="<?php echo onoff_map_esc_attr($map_data_url); ?>"
     data-map-radius="<?php echo onoff_map_esc_attr($map_radius); ?>"
     data-map-use-location="<?php echo $map_cfg['use_current_location'] ? '1' : '0'; ?>"
     <?php if ($map_locations_json !== '') { ?>data-map-locations="<?php echo onoff_map_esc_attr($map_locations_json); ?>"<?php } ?>>

    <?php if (!$map_has_key) { ?>
    <div class="map-placeholder" role="status">
        <p class="map-placeholder__title"><?php echo get_text($map_cfg['placeholder_title']); ?></p>
        <p class="map-placeholder__desc"><?php echo get_text($map_cfg['placeholder_desc']); ?></p>
        <p class="map-placeholder__hint"><code>_site.config.php</code> → <strong>google_maps_api_key</strong></p>
    </div>
    <?php } else { ?>
    <div id="<?php echo onoff_map_esc_attr($map_canvas_id); ?>" class="google-map" aria-label="지도"></div>
    <p class="map-module__loading sound_only">지도를 불러오는 중입니다.</p>
    <?php } ?>
</div>
