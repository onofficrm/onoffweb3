<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_SKIN_PATH.'/board/_inc/g5b-fallback.php');

/**
 * 목록 썸네일 HTML (첨부 → 본문 첫 이미지 → /img/common/no-image.svg → empty 박스)
 */
function g5b_list_thumb_html($bo_table, $wr_id, $width, $height, $subject = '', $is_secret = false, $is_notice = false, $is_crop = true)
{
    if (!function_exists('get_list_thumbnail')) {
        include_once(G5_LIB_PATH.'/thumbnail.lib.php');
    }

    if ($is_secret) {
        return '<span class="board-thumb board-thumb--empty board-thumb--secret" title="비밀글">'
            .'<i class="fa fa-lock" aria-hidden="true"></i><span class="sound_only">비밀글</span></span>';
    }

    if ($is_notice) {
        $thumb = get_list_thumbnail($bo_table, $wr_id, $width, $height, false, $is_crop);
        if (empty($thumb['src'])) {
            return '<span class="board-thumb board-thumb--notice"><span class="notice_icon board-badge board-badge--notice">공지</span></span>';
        }
    }

    $thumb = get_list_thumbnail($bo_table, $wr_id, $width, $height, false, $is_crop);
    $alt = isset($thumb['alt']) && $thumb['alt'] ? get_text($thumb['alt']) : get_text(strip_tags($subject));

    if (!empty($thumb['src'])) {
        $src = $thumb['src'];
        $tag = '<img src="'.htmlspecialchars($src, ENT_QUOTES).'" alt="'.htmlspecialchars($alt, ENT_QUOTES).'" class="board-thumb__img" loading="lazy" decoding="async">';
        return '<span class="board-thumb board-thumb--has-img">'.$tag.'</span>';
    }

    if (g5b_fallback_file_exists('image')) {
        return '<span class="board-thumb board-thumb--fallback">'
            .g5b_fallback_img_html('image', 'board-thumb__img board-thumb__img--placeholder')
            .'</span>';
    }

    return '<span class="board-thumb board-thumb--empty" aria-hidden="true"></span>';
}
