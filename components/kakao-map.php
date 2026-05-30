<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$cmp_map_key = g5site_cfg('kakao_map_key', '');
$cmp_address = g5site_cfg('address', '주소를 입력하세요');
$cmp_lat = g5site_cfg('kakao_map_lat', '37.5665');
$cmp_lng = g5site_cfg('kakao_map_lng', '126.9780');
?>

<section class="cmp-kakao-map" aria-label="오시는 길">
    <div class="cmp-kakao-map__inner">
        <?php if ($cmp_map_key !== '') { ?>
        <!--
          카카오맵 연동:
          1. _site.config.php → kakao_map_key 에 JavaScript 키 입력
          2. 아래 div id="cmpKakaoMapCanvas" 에 maps SDK로 지도 생성
          3. 예: https://apis.map.kakao.com/web/guide/
        -->
        <div id="cmpKakaoMapCanvas" class="cmp-kakao-map__canvas" data-lat="<?php echo htmlspecialchars($cmp_lat, ENT_QUOTES, 'UTF-8'); ?>" data-lng="<?php echo htmlspecialchars($cmp_lng, ENT_QUOTES, 'UTF-8'); ?>"></div>
        <script src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?php echo htmlspecialchars($cmp_map_key, ENT_QUOTES, 'UTF-8'); ?>&autoload=false"></script>
        <?php } else { ?>
        <div class="cmp-kakao-map__placeholder">
            <p class="cmp-kakao-map__placeholder-title"><i class="fa fa-map-marker" aria-hidden="true"></i> 지도 영역</p>
            <p class="cmp-kakao-map__placeholder-desc">카카오 JavaScript 키가 설정되면 지도가 표시됩니다.</p>
            <p class="cmp-kakao-map__placeholder-hint"><code>_site.config.php</code> → <strong>kakao_map_key</strong> 항목에 키를 입력하세요.</p>
            <address class="cmp-kakao-map__address"><?php echo htmlspecialchars($cmp_address, ENT_QUOTES, 'UTF-8'); ?></address>
        </div>
        <?php } ?>
    </div>
</section>
