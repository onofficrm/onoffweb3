<?php
/**
 * 마커 정보창 HTML 템플릿
 * - JS createMarkerInfoContent() 와 동일 필드 구조
 * - 출력 시 get_text() / htmlspecialchars() 사용 (XSS 방지)
 *
 * @param array<string, mixed> $loc name, category, address, phone, distance, link, hours, description
 * @return string HTML
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_map_marker_info_html')) {
    function onoff_map_marker_info_html(array $loc)
    {
        $name = isset($loc['name']) ? get_text($loc['name']) : '';
        $category = isset($loc['category']) ? get_text($loc['category']) : '';
        $address = isset($loc['address']) ? get_text($loc['address']) : '';
        $phone = isset($loc['phone']) ? get_text($loc['phone']) : '';
        $hours = isset($loc['hours']) ? get_text($loc['hours']) : '';
        $distance = isset($loc['distance']) ? get_text((string) $loc['distance']) : '';
        $link = isset($loc['link']) ? $loc['link'] : '';
        $lat = isset($loc['lat']) ? $loc['lat'] : '';
        $lng = isset($loc['lng']) ? $loc['lng'] : '';

        if ($name === '') {
            $name = '장소';
        }

        $dir_url = '#';
        if (is_numeric($lat) && is_numeric($lng)) {
            $dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($lat . ',' . $lng);
        } elseif ($address !== '') {
            $dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($address);
        }

        $tel_href = '#';
        if ($phone !== '') {
            $digits = preg_replace('/[^0-9+]/', '', $phone);
            $tel_href = $digits !== '' ? 'tel:' . $digits : '#';
        }

        ob_start();
        ?>
        <div class="marker-info">
            <h3 class="marker-info-title"><?php echo $name; ?></h3>
            <?php if ($category !== '') { ?>
            <p class="marker-info-category"><?php echo $category; ?></p>
            <?php } ?>
            <?php if ($address !== '') { ?>
            <p class="marker-info-address"><?php echo $address; ?></p>
            <?php } ?>
            <?php if ($phone !== '') { ?>
            <p class="marker-info-phone"><a href="<?php echo htmlspecialchars($tel_href, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $phone; ?></a></p>
            <?php } ?>
            <?php if ($hours !== '') { ?>
            <p class="marker-info-hours"><?php echo $hours; ?></p>
            <?php } ?>
            <?php if ($distance !== '') { ?>
            <p class="marker-info-distance"><?php echo $distance; ?></p>
            <?php } ?>
            <div class="marker-info-actions">
                <?php if ($link !== '' && $link !== '#') { ?>
                <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>" class="marker-info-link">상세보기</a>
                <?php } ?>
                <?php if ($dir_url !== '#') { ?>
                <a href="<?php echo htmlspecialchars($dir_url, ENT_QUOTES, 'UTF-8'); ?>" class="marker-info-directions" target="_blank" rel="noopener noreferrer">길찾기</a>
                <?php } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
