<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 게시판 스킨 공통 fallback 이미지 경로
 * - /img/common/no-image.svg   (썸네일·이미지형)
 * - /img/common/no-youtube.svg (유튜브형)
 */
define('G5B_FALLBACK_DIR', G5_PATH.'/img/common');
define('G5B_FALLBACK_NO_IMAGE_FILE', G5B_FALLBACK_DIR.'/no-image.svg');
define('G5B_FALLBACK_NO_YOUTUBE_FILE', G5B_FALLBACK_DIR.'/no-youtube.svg');
define('G5B_FALLBACK_NO_IMAGE_URL', G5_IMG_URL.'/common/no-image.svg');
define('G5B_FALLBACK_NO_YOUTUBE_URL', G5_IMG_URL.'/common/no-youtube.svg');

/**
 * @param string $type image|youtube
 */
function g5b_fallback_file_path($type = 'image')
{
    return ($type === 'youtube') ? G5B_FALLBACK_NO_YOUTUBE_FILE : G5B_FALLBACK_NO_IMAGE_FILE;
}

/**
 * @param string $type image|youtube
 */
function g5b_fallback_file_url($type = 'image')
{
    return ($type === 'youtube') ? G5B_FALLBACK_NO_YOUTUBE_URL : G5B_FALLBACK_NO_IMAGE_URL;
}

/**
 * @param string $type image|youtube
 */
function g5b_fallback_file_exists($type = 'image')
{
    return is_file(g5b_fallback_file_path($type));
}

/**
 * fallback <img> 태그 HTML
 * @param string $type image|youtube
 */
function g5b_fallback_img_html($type = 'image', $class = 'board-fallback__img')
{
    if (!g5b_fallback_file_exists($type)) {
        return '';
    }
    $src = g5b_fallback_file_url($type);
    $label = ($type === 'youtube') ? 'NO VIDEO' : 'NO IMAGE';
    $class = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $class);

    return '<img src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'" alt="" role="presentation" class="'.htmlspecialchars(trim($class), ENT_QUOTES, 'UTF-8').' board-thumb__img--placeholder" loading="lazy" decoding="async">';
}
