<?php
/**
 * 내 주변 장소 찾기 UI
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/map-config.php';

$map_cfg = onoff_map_get_config();
$map_categories = isset($map_categories) && is_array($map_categories) ? $map_categories : array('병원', '학원', '마사지', '음식점', '여행지');
$map_data_url = isset($map_data_url) ? $map_data_url : $map_cfg['data_url_default'];
?>

<div class="store-locator"
     data-map-lat="<?php echo onoff_map_esc_attr($map_cfg['default_lat']); ?>"
     data-map-lng="<?php echo onoff_map_esc_attr($map_cfg['default_lng']); ?>"
     data-map-zoom="<?php echo onoff_map_esc_attr($map_cfg['default_zoom']); ?>"
     data-map-data-url="<?php echo onoff_map_esc_attr($map_data_url); ?>"
     data-map-radius="<?php echo onoff_map_esc_attr($map_cfg['default_radius_km']); ?>"
     data-map-use-location="<?php echo $map_cfg['use_current_location'] ? '1' : '0'; ?>">

    <div class="locator-controls">
        <div class="locator-controls__row locator-search">
            <label for="locatorSearch" class="sound_only">키워드 검색</label>
            <input type="search" id="locatorSearch" class="locator-search__input" placeholder="장소명·주소·태그 검색" autocomplete="off">
        </div>
        <div class="locator-controls__row locator-controls__filters">
            <div class="locator-category">
                <label for="locatorCategory" class="locator-category__label">카테고리</label>
                <select id="locatorCategory" class="locator-category__select">
                    <option value="">전체</option>
                    <?php foreach ($map_categories as $cat) {
                        $cat = get_text($cat);
                        if ($cat === '') {
                            continue;
                        }
                        ?>
                    <option value="<?php echo onoff_map_esc_attr($cat); ?>"><?php echo $cat; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="locator-radius">
                <label for="locatorRadius" class="locator-radius__label">반경</label>
                <select id="locatorRadius" class="locator-radius__select">
                    <option value="1">1 km</option>
                    <option value="3">3 km</option>
                    <option value="5" selected>5 km</option>
                    <option value="10">10 km</option>
                    <option value="20">20 km</option>
                    <option value="0">제한 없음</option>
                </select>
            </div>
            <?php if ($map_cfg['use_current_location']) { ?>
            <button type="button" class="locator-current-location btn btn--primary">
                <span class="locator-current-location__text">현재 위치</span>
            </button>
            <?php } ?>
        </div>
        <p class="locator-controls__hint">위치 권한을 허용하지 않아도 기본 지역 기준으로 장소를 볼 수 있습니다.</p>
    </div>

    <div class="locator-layout">
        <div class="locator-results-wrap">
            <h2 class="locator-results__heading">주변 장소</h2>
            <p class="locator-results__status" aria-live="polite"></p>
            <ul class="locator-results" role="list"></ul>
            <p class="locator-results__empty" hidden>조건에 맞는 장소가 없습니다.</p>
        </div>
        <div class="locator-map-wrap">
            <?php
            $map_module_id = 'locator';
            include dirname(__FILE__) . '/google-map.php';
            ?>
        </div>
    </div>
</div>
