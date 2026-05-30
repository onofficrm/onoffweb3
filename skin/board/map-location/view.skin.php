<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/map.css">', 1);

$loc_cat    = isset($view['wr_1']) ? get_text($view['wr_1']) : '';
$loc_addr   = isset($view['wr_2']) ? get_text($view['wr_2']) : '';
$loc_lat_s  = isset($view['wr_3']) ? trim($view['wr_3']) : '';
$loc_lng_s  = isset($view['wr_4']) ? trim($view['wr_4']) : '';
$loc_lat    = is_numeric($loc_lat_s) ? (float) $loc_lat_s : 0;
$loc_lng    = is_numeric($loc_lng_s) ? (float) $loc_lng_s : 0;
$loc_phone  = isset($view['wr_5']) ? get_text($view['wr_5']) : '';
$loc_hours  = isset($view['wr_6']) ? get_text($view['wr_6']) : '';
$loc_link   = isset($view['wr_7']) ? get_text($view['wr_7']) : '';
$loc_region = isset($view['wr_9']) ? get_text($view['wr_9']) : '';

$has_coords = ($loc_lat_s !== '' && $loc_lng_s !== '') && abs($loc_lat) <= 90 && abs($loc_lng) <= 180;

if (is_file(G5_PATH.'/components/maps/map-config.php')) {
    include_once(G5_PATH.'/components/maps/map-config.php');
}
$map_has_key = function_exists('onoff_map_has_api_key') ? onoff_map_has_api_key() : false;

add_javascript('<script src="'.G5_JS_URL.'/google-map.js"></script>', 25);
if ($map_has_key && $has_coords && function_exists('onoff_map_get_config')) {
    $map_cfg_view = onoff_map_get_config();
    $map_script_key = htmlspecialchars($map_cfg_view['api_key'], ENT_QUOTES, 'UTF-8');
    add_javascript(
        '<script src="https://maps.googleapis.com/maps/api/js?key='.$map_script_key.'&amp;callback=initOnOffGoogleMap" defer></script>',
        5
    );
}

$tel_href = '#';
if ($loc_phone !== '' && function_exists('g5site_tel_link')) {
    $tel_href = g5site_tel_link($loc_phone);
} elseif ($loc_phone !== '') {
    $digits = preg_replace('/[^0-9+]/', '', $loc_phone);
    $tel_href = $digits !== '' ? 'tel:'.$digits : '#';
}

$dir_url = '#';
if ($has_coords) {
    $dir_url = 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($loc_lat.','.$loc_lng);
} elseif ($loc_addr !== '') {
    $dir_url = 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($loc_addr);
}
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article class="board-wrap board-wrap--map-location board-view" id="bo_v" style="width:<?php echo $width; ?>">

    <header class="board-view__head">
        <h1 class="board-title" id="bo_v_title">
            <?php if ($category_name) { ?><span class="bo_v_cate"><?php echo $view['ca_name']; ?></span><?php } ?>
            <span class="bo_v_tit"><?php echo get_text(cut_str($view['wr_subject'], 70)); ?></span>
        </h1>
        <?php if ($loc_cat) { ?><p class="map-location-view__category"><?php echo $loc_cat; ?></p><?php } ?>
    </header>

    <section class="map-location-view__map" id="map-location-view-map">
        <?php
        if ($has_coords && is_file(G5_PATH.'/components/maps/google-map.php')) {
            $map_module_id = 'view_'.$view['wr_id'];
            $map_lat = $loc_lat;
            $map_lng = $loc_lng;
            $map_zoom = 15;
            $map_locations = array(array(
                'id'       => $view['wr_id'],
                'name'     => get_text($view['wr_subject']),
                'category' => $loc_cat,
                'address'  => $loc_addr,
                'lat'      => $loc_lat,
                'lng'      => $loc_lng,
                'phone'    => $loc_phone,
                'hours'    => $loc_hours,
                'link'     => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$view['wr_id'],
            ));
            $map_cfg = function_exists('onoff_map_get_config') ? onoff_map_get_config() : array();
            $map_data_url = '';
            include G5_PATH.'/components/maps/google-map.php';
        } elseif ($loc_addr !== '') {
            echo '<p class="map-placeholder__desc">좌표가 없어 지도를 표시할 수 없습니다. 주소: '.get_text($loc_addr).'</p>';
        } else {
            echo '<p class="map-placeholder__desc">위도·경도(wr_3, wr_4)를 입력하면 지도가 표시됩니다.</p>';
        }
        ?>
    </section>

    <dl class="map-location-meta">
        <?php if ($loc_addr) { ?><div><dt>주소</dt><dd><?php echo $loc_addr; ?></dd></div><?php } ?>
        <?php if ($loc_region) { ?><div><dt>지역</dt><dd><?php echo $loc_region; ?></dd></div><?php } ?>
        <?php if ($loc_phone) { ?><div><dt>전화</dt><dd><a href="<?php echo htmlspecialchars($tel_href, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $loc_phone; ?></a></dd></div><?php } ?>
        <?php if ($loc_hours) { ?><div><dt>영업시간</dt><dd><?php echo $loc_hours; ?></dd></div><?php } ?>
    </dl>

    <div class="map-location-actions">
        <?php if ($tel_href !== '#') { ?><a href="<?php echo htmlspecialchars($tel_href, ENT_QUOTES, 'UTF-8'); ?>" class="btn_b01 btn">전화</a><?php } ?>
        <?php if ($dir_url !== '#') { ?><a href="<?php echo htmlspecialchars($dir_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn_b01 btn" target="_blank" rel="noopener noreferrer">길찾기</a><?php } ?>
        <?php if ($loc_link !== '') { ?><a href="<?php echo htmlspecialchars($loc_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn_b01 btn" target="_blank" rel="noopener noreferrer">상세링크</a><?php } ?>
    </div>

    <section class="board-view__body" id="bo_v_atc">
        <div id="bo_v_con" class="board-view__content"><?php echo get_view_thumbnail($view['content']); ?></div>
    </section>

    <?php
    /*
     * LocalBusiness Schema — components/schema/local-business.php 연동 가능
     * $local_business_type = 'LocalBusiness';
     */
    ?>

    <footer class="board-view__foot">
        <ul class="board-actions btn_bo_user">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01 btn">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;" class="btn_b01 btn">삭제</a></li><?php } ?>
        </ul>
    </footer>
</article>
