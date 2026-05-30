<?php
include_once(dirname(__FILE__).'/_init.php');

$page_description = 'Google Maps 기반 지역 장소 찾기 샘플 페이지입니다. API 키 설정 후 내 주변 장소를 확인할 수 있습니다.';

if (is_file(G5_PATH.'/components/maps/map-config.php')) {
    include_once(G5_PATH.'/components/maps/map-config.php');
}

$map_cfg = function_exists('onoff_map_get_config') ? onoff_map_get_config() : array();
$map_has_key = function_exists('onoff_map_has_api_key') ? onoff_map_has_api_key() : false;

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/map.css">', 15);
add_javascript('<script src="'.G5_JS_URL.'/google-map.js"></script>', 25);

if ($map_has_key && !empty($map_cfg['api_key'])) {
    $map_script_key = htmlspecialchars($map_cfg['api_key'], ENT_QUOTES, 'UTF-8');
    add_javascript(
        '<script src="https://maps.googleapis.com/maps/api/js?key='.$map_script_key.'&amp;callback=initOnOffGoogleMap" defer></script>',
        5
    );
}

g5_page_start('내 주변 장소 찾기');
?>
<div class="page-template page-map-locator">
  <header class="page-hero reveal">
    <div class="page-inner">
      <p class="page-eyebrow">Map Locator</p>
      <h1 class="page-title">내 주변 장소 찾기</h1>
      <p class="page-desc">현재 위치를 허용하면 가까운 장소를 확인할 수 있습니다. 위치 권한을 허용하지 않아도 기본 지역 기준으로 샘플 장소를 볼 수 있습니다.</p>
      <?php if (!$map_has_key) { ?>
      <p class="page-map-locator__notice" role="status">
        <strong>안내:</strong> Google Maps API 키가 없어 지도 placeholder가 표시됩니다.
        운영 전 <code>_site.config.php</code>의 <code>google_maps_api_key</code>를 입력하세요.
      </p>
      <?php } ?>
    </div>
  </header>

  <section class="page-section page-map-locator__module reveal">
    <div class="page-inner page-inner--wide">
      <?php
      if (is_file(G5_PATH.'/components/maps/store-locator.php')) {
          include_once(G5_PATH.'/components/maps/store-locator.php');
      } else {
          echo '<p class="page-map-locator__error">지도 모듈 파일을 찾을 수 없습니다.</p>';
      }
      ?>
    </div>
  </section>

  <section class="page-section page-map-locator__guide reveal">
    <div class="page-inner">
      <h2 class="page-section__title">사용 안내</h2>
      <ul class="page-map-locator__list">
        <li>장소 데이터 샘플: <code>/data/map-locations.sample.json</code></li>
        <li>게시판 연동 스킨: <code>map-location</code> (권장 <code>bo_table=location</code>)</li>
        <li>상세 가이드: <code>MAP-GUIDE.md</code>, 빌더 연동: <code>MAP-BUILDER-WORKFLOW.md</code></li>
        <li>API 키는 Google Cloud Console에서 발급·도메인 제한을 권장합니다.</li>
      </ul>
    </div>
  </section>
</div>
<?php
g5_page_end();
