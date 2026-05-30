<?php
/**
 * 게시판 글보기 SEO — Article/Breadcrumb Schema, 관련글
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/g5b-seo-list.php';

/* iCRM DB 직접 발행: 글보기 시 wr_seo_title 보정 (커스텀 스킨 공통) */
if (function_exists('g5site_cfg_bool') && g5site_cfg_bool('icrm_builtin', true)) {
    if (is_file(G5_LIB_PATH.'/icrm.lib.php')) {
        include_once G5_LIB_PATH.'/icrm.lib.php';
        if (function_exists('icrm_ensure_wr_seo_title_on_view')) {
            icrm_ensure_wr_seo_title_on_view();
        }
        if (function_exists('icrm_enqueue_board_assets')) {
            icrm_enqueue_board_assets();
        }
    }
}

if (!function_exists('g5b_seo_view_article_image')) {
    /**
     * 대표 이미지 URL (첨부 썸네일)
     *
     * @param string $bo_table
     * @param array  $view
     * @return string
     */
    function g5b_seo_view_article_image($bo_table, $view)
    {
        if (empty($view['wr_id'])) {
            return '';
        }

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH . '/thumbnail.lib.php';
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '') {
            return '';
        }

        $thumb = get_list_thumbnail($bo_table, (int) $view['wr_id'], 800, 600, false, true);
        if (!empty($thumb['src'])) {
            return $thumb['src'];
        }

        return '';
    }
}

if (!function_exists('g5b_seo_view_prepare_article')) {
    /**
     * Article Schema용 전역 변수 설정
     *
     * @param array  $view
     * @param string $bo_table
     * @param int    $wr_id
     */
    function g5b_seo_view_prepare_article($view, $bo_table, $wr_id)
    {
        global $article_title, $article_description, $article_url, $article_image,
               $article_date_published, $article_date_modified, $article_author_name;

        $article_title = !empty($view['wr_subject']) ? get_text($view['wr_subject']) : '';

        $plain = '';
        if (!empty($view['wr_content'])) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($view['wr_content'])));
        }
        if ($plain !== '') {
            $article_description = function_exists('cut_str') ? cut_str($plain, 180) : substr($plain, 0, 180);
        } else {
            $article_description = '';
        }

        $bo_table_safe = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $article_url = '';
        if ($bo_table_safe !== '' && $wr_id > 0 && function_exists('get_pretty_url')) {
            $article_url = get_pretty_url($bo_table_safe, $wr_id);
        }
        if ($article_url === '' && $bo_table_safe !== '' && $wr_id > 0) {
            $article_url = G5_BBS_URL . '/board.php?bo_table=' . urlencode($bo_table_safe) . '&wr_id=' . (int) $wr_id;
        }

        $article_image = g5b_seo_view_article_image($bo_table_safe, $view);

        $article_date_published = '';
        if (!empty($view['wr_datetime'])) {
            $ts = strtotime($view['wr_datetime']);
            if ($ts !== false) {
                $article_date_published = date('c', $ts);
            }
        }

        $article_date_modified = $article_date_published;
        if (!empty($view['wr_last'])) {
            $ts = strtotime($view['wr_last']);
            if ($ts !== false) {
                $article_date_modified = date('c', $ts);
            }
        }

        $article_author_name = !empty($view['wr_name']) ? get_text($view['wr_name']) : '';
    }
}

if (!function_exists('g5b_seo_view_breadcrumb_items')) {
    /**
     * @param array  $board
     * @param array  $view
     * @param string $bo_table
     * @param int    $wr_id
     * @return array
     */
    function g5b_seo_view_breadcrumb_items($board, $view, $bo_table, $wr_id)
    {
        $bo_table_safe = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $list_url = G5_BBS_URL . '/board.php?bo_table=' . urlencode($bo_table_safe);
        if (function_exists('get_pretty_url') && $bo_table_safe !== '') {
            $pretty = get_pretty_url($bo_table_safe);
            if ($pretty) {
                $list_url = $pretty;
            }
        }

        $view_url = $list_url;
        if ($wr_id > 0 && function_exists('get_pretty_url') && $bo_table_safe !== '') {
            $view_url = get_pretty_url($bo_table_safe, $wr_id);
        } elseif ($wr_id > 0) {
            $view_url .= '&wr_id=' . (int) $wr_id;
        }

        $board_name = isset($board['bo_subject']) ? get_text($board['bo_subject']) : '게시판';
        $post_title = !empty($view['wr_subject']) ? get_text($view['wr_subject']) : '';

        return array(
            array('name' => '홈', 'url' => defined('G5_URL') ? G5_URL : '/'),
            array('name' => $board_name, 'url' => $list_url),
            array('name' => $post_title, 'url' => $view_url),
        );
    }
}

if (!function_exists('g5b_seo_view_footer')) {
    /**
     * Schema + 관련글 (글보기 하단)
     *
     * @param array $view
     * @param array $board
     * @param string $bo_table
     * @param int $wr_id
     * @param array $opts article, breadcrumb, related, related_title, related_limit
     */
    function g5b_seo_view_footer($view, $board, $bo_table, $wr_id, $opts = array())
    {
        $article_on = !isset($opts['article']) || $opts['article'];
        $breadcrumb_on = !isset($opts['breadcrumb']) || $opts['breadcrumb'];
        $related_on = !empty($opts['related']);
        $related_title = isset($opts['related_title']) ? $opts['related_title'] : '관련 글';
        $related_limit = isset($opts['related_limit']) ? (int) $opts['related_limit'] : 4;

        $schema_dir = G5_PATH . '/components/schema/';
        $article_file = $schema_dir . 'article.php';
        $breadcrumb_file = $schema_dir . 'breadcrumb.php';
        $related_file = G5_PATH . '/components/related-posts.php';

        if ($related_on && is_file($related_file)) {
            echo '<div class="related-posts-wrap board-seo-related">';
            $related_bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
            $related_keyword = !empty($view['wr_subject']) ? get_text(strip_tags($view['wr_subject'])) : '';
            $related_exclude_wr_id = (int) $wr_id;
            $related_title = $related_title;
            $related_limit = max(1, min(8, $related_limit));
            include_once $related_file;
            echo '</div>';
        }

        if ($breadcrumb_on && is_file($breadcrumb_file)) {
            $breadcrumb_items = g5b_seo_view_breadcrumb_items($board, $view, $bo_table, $wr_id);
            include $breadcrumb_file;
        }

        if ($article_on && is_file($article_file)) {
            g5b_seo_view_prepare_article($view, $bo_table, $wr_id);
            include $article_file;
        }
    }
}

if (!function_exists('g5b_seo_view_modified_time')) {
    /**
     * 수정일 표시 (wr_last가 wr_datetime과 다를 때)
     *
     * @param array $view
     * @return string HTML or empty
     */
    function g5b_seo_view_modified_time($view)
    {
        if (empty($view['wr_last']) || empty($view['wr_datetime'])) {
            return '';
        }

        $pub = strtotime($view['wr_datetime']);
        $mod = strtotime($view['wr_last']);
        if ($pub === false || $mod === false || $mod <= $pub) {
            return '';
        }

        return '<li class="if_modified"><span class="sound_only">수정일</span>'
            . g5b_seo_time_tag($view['wr_last'], date('Y-m-d H:i', $mod))
            . '</li>';
    }
}
