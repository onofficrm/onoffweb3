<?php
/**
 * 관련글·최신글·분류글 컴포넌트 공통 헬퍼
 * - related-posts.php, latest-posts.php, category-posts.php 에서 include
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5b_content_sanitize_bo_table')) {
    function g5b_content_sanitize_bo_table($bo_table)
    {
        return preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
    }
}

if (!function_exists('g5b_content_board_available')) {
    function g5b_content_board_available($bo_table)
    {
        if (function_exists('g5_sample_board_available')) {
            return g5_sample_board_available($bo_table);
        }

        global $g5;

        $bo_table = g5b_content_sanitize_bo_table($bo_table);
        if ($bo_table === '') {
            return false;
        }

        if (!function_exists('get_board_db')) {
            return false;
        }

        $board = get_board_db($bo_table, true);
        if (empty($board['bo_table'])) {
            return false;
        }

        $write_table = $g5['write_prefix'] . $bo_table;
        $check = sql_fetch(" SHOW TABLES LIKE '{$write_table}' ", false);

        return is_array($check) && !empty($check);
    }
}

if (!function_exists('g5b_content_post_href')) {
    function g5b_content_post_href($bo_table, $wr_id)
    {
        $bo_table = g5b_content_sanitize_bo_table($bo_table);
        $wr_id = (int) $wr_id;

        if ($bo_table === '' || $wr_id < 1) {
            return '';
        }

        if (function_exists('get_pretty_url')) {
            $url = get_pretty_url($bo_table, $wr_id);
            if ($url) {
                return $url;
            }
        }

        return G5_BBS_URL . '/board.php?bo_table=' . urlencode($bo_table) . '&amp;wr_id=' . $wr_id;
    }
}

if (!function_exists('g5b_content_excerpt')) {
    function g5b_content_excerpt($content, $len = 80)
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $content)));
        if ($plain === '') {
            return '';
        }

        if (function_exists('cut_str')) {
            return cut_str($plain, (int) $len);
        }

        if (function_exists('mb_strlen') && mb_strlen($plain, 'UTF-8') > $len) {
            return mb_substr($plain, 0, (int) $len, 'UTF-8') . '…';
        }

        if (strlen($plain) > $len) {
            return substr($plain, 0, (int) $len) . '…';
        }

        return $plain;
    }
}

if (!function_exists('g5b_content_thumb_html')) {
    function g5b_content_thumb_html($bo_table, $wr_id, $subject = '', $wr_option = '')
    {
        $is_secret = (strpos((string) $wr_option, 'secret') !== false);

        if (is_file(G5_SKIN_PATH . '/board/_inc/g5b-thumb.php')) {
            include_once G5_SKIN_PATH . '/board/_inc/g5b-thumb.php';
            if (function_exists('g5b_list_thumb_html')) {
                return g5b_list_thumb_html($bo_table, $wr_id, 400, 260, $subject, $is_secret, false, true);
            }
        }

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH . '/thumbnail.lib.php';
        }

        if ($is_secret) {
            return '<span class="content-card__no-image content-card__no-image--secret" aria-hidden="true"><i class="fa fa-lock" aria-hidden="true"></i></span>';
        }

        $thumb = get_list_thumbnail($bo_table, $wr_id, 400, 260, false, true);
        $alt = !empty($thumb['alt']) ? get_text($thumb['alt']) : get_text(strip_tags($subject));

        if (!empty($thumb['src'])) {
            return '<img src="' . htmlspecialchars($thumb['src'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="content-card__img" loading="lazy" decoding="async">';
        }

        if (is_file(G5_PATH . '/img/common/no-image.svg')) {
            return '<img src="' . htmlspecialchars(G5_URL . '/img/common/no-image.svg', ENT_QUOTES, 'UTF-8') . '" alt="" class="content-card__img content-card__img--placeholder" loading="lazy" decoding="async">';
        }

        return '<span class="content-card__no-image" aria-hidden="true"></span>';
    }
}

/**
 * 게시글 목록 조회 (가벼운 단일 쿼리)
 *
 * @param string $bo_table
 * @param int    $limit
 * @param array  $args keyword, ca_name, exclude_wr_id
 * @return array
 */
if (!function_exists('g5b_content_fetch_posts')) {
    function g5b_content_fetch_posts($bo_table, $limit = 5, $args = array())
    {
        global $g5;

        $bo_table = g5b_content_sanitize_bo_table($bo_table);
        $limit = max(1, min(20, (int) $limit));

        if ($bo_table === '' || !g5b_content_board_available($bo_table)) {
            return array();
        }

        $write_table = $g5['write_prefix'] . $bo_table;
        $where = " wr_is_comment = 0 ";
        $where .= " and (wr_option not like '%secret%' or wr_option = '' or wr_option is null) ";

        if (!empty($args['exclude_wr_id'])) {
            $where .= ' and wr_id <> ' . (int) $args['exclude_wr_id'];
        }

        if (!empty($args['ca_name'])) {
            $ca_name = sql_real_escape_string(trim(strip_tags((string) $args['ca_name'])));
            if ($ca_name !== '') {
                $where .= " and ca_name = '{$ca_name}' ";
            }
        }

        if (!empty($args['keyword'])) {
            $keyword = trim(strip_tags((string) $args['keyword']));
            if (function_exists('mb_strlen') && mb_strlen($keyword, 'UTF-8') > 40) {
                $keyword = mb_substr($keyword, 0, 40, 'UTF-8');
            } elseif (strlen($keyword) > 40) {
                $keyword = substr($keyword, 0, 40);
            }
            if ($keyword !== '') {
                $kw = sql_real_escape_string('%' . $keyword . '%');
                $where .= " and (wr_subject like '{$kw}' or wr_content like '{$kw}') ";
            }
        }

        $sql = " select wr_id, wr_subject, wr_content, wr_datetime, wr_option, ca_name
                 from {$write_table}
                 where {$where}
                 order by wr_num desc, wr_reply asc
                 limit {$limit} ";

        $result = sql_query($sql, false);
        if (!$result) {
            return array();
        }

        $rows = array();
        while ($row = sql_fetch_array($result)) {
            if (empty($row['wr_id'])) {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }
}

/**
 * @param string $block_class related-posts|latest-posts|category-posts
 * @param string $title
 * @param array  $posts
 * @param string $layout card|list
 */
if (!function_exists('g5b_content_render_posts')) {
    function g5b_content_render_posts($block_class, $title, $posts, $layout = 'card', $bo_table = '')
    {
        if (empty($posts) || !is_array($posts)) {
            return;
        }

        $block_class = preg_replace('/[^a-z0-9_-]/', '', (string) $block_class);
        $layout = ($layout === 'list') ? 'list' : 'card';
        $title = trim(strip_tags((string) $title));
        $bo_table = g5b_content_sanitize_bo_table($bo_table);

        $list_class = 'content-list content-list--' . $layout . ' ' . $block_class . '__list';

        echo '<section class="' . htmlspecialchars($block_class, ENT_QUOTES, 'UTF-8') . ' content-block" aria-labelledby="' . htmlspecialchars($block_class, ENT_QUOTES, 'UTF-8') . '-title">';
        echo '<div class="content-block__inner">';
        if ($title !== '') {
            echo '<h2 class="content-block__title" id="' . htmlspecialchars($block_class, ENT_QUOTES, 'UTF-8') . '-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
        }
        echo '<ul class="' . htmlspecialchars($list_class, ENT_QUOTES, 'UTF-8') . '">';

        foreach ($posts as $row) {
            $wr_id = (int) $row['wr_id'];
            $href = g5b_content_post_href($bo_table, $wr_id);
            if ($href === '') {
                continue;
            }

            $subject = get_text($row['wr_subject']);
            $excerpt = g5b_content_excerpt(isset($row['wr_content']) ? $row['wr_content'] : '', 90);
            $datetime = !empty($row['wr_datetime']) ? $row['wr_datetime'] : '';
            $time_attr = $datetime !== '' ? date('c', strtotime($datetime)) : '';
            $time_display = $datetime !== '' ? date('Y-m-d', strtotime($datetime)) : '';
            $thumb = g5b_content_thumb_html($bo_table, $wr_id, $subject, isset($row['wr_option']) ? $row['wr_option'] : '');

            echo '<li class="content-list__item">';
            echo '<article class="content-card">';
            echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="content-card__link">';
            echo '<div class="content-card__media">' . $thumb . '</div>';
            echo '<div class="content-card__body">';
            echo '<h3 class="content-card__title">' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</h3>';
            if ($excerpt !== '') {
                echo '<p class="content-card__excerpt">' . htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if ($time_display !== '') {
                echo '<time class="content-card__date" datetime="' . htmlspecialchars($time_attr, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($time_display, ENT_QUOTES, 'UTF-8') . '</time>';
            }
            echo '</div></a></article></li>';
        }

        echo '</ul></div></section>';
    }
}
