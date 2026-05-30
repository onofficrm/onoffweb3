<?php
/**
 * 게시판 목록 SEO 헬퍼 (h1, time 태그)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('g5b_seo_list_h1')) {
    /**
     * 게시판 목록 h1
     *
     * @param array  $board
     * @param string $class
     */
    function g5b_seo_list_h1($board, $class = 'board-list__h1')
    {
        $class = preg_replace('/[^a-zA-Z0-9_\- ]/', '', (string) $class);
        $title = isset($board['bo_subject']) ? get_text($board['bo_subject']) : '';

        if ($title === '') {
            return;
        }

        echo '<h1 class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
    }
}

if (!function_exists('g5b_seo_time_tag')) {
    /**
     * @param string $datetime wr_datetime 등
     * @param string $display  화면용 (비우면 datetime에서 생성)
     * @return string HTML
     */
    function g5b_seo_time_tag($datetime, $display = '')
    {
        $datetime = trim((string) $datetime);
        $display = (string) $display;

        if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
            if ($display !== '') {
                return '<time class="post-date board-meta__time">' . htmlspecialchars($display, ENT_QUOTES, 'UTF-8') . '</time>';
            }

            return '';
        }

        $ts = strtotime($datetime);
        if ($ts === false) {
            $text = $display !== '' ? $display : $datetime;

            return '<time class="post-date board-meta__time">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</time>';
        }

        $iso = date('c', $ts);
        $text = $display !== '' ? $display : date('Y-m-d H:i', $ts);

        return '<time class="post-date board-meta__time" datetime="' . htmlspecialchars($iso, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</time>';
    }
}

if (!function_exists('g5b_seo_list_time')) {
    /**
     * 목록 행 날짜
     *
     * @param array $item $list[$i]
     * @return string
     */
    function g5b_seo_list_time($item)
    {
        $dt = !empty($item['wr_datetime']) ? $item['wr_datetime'] : '';
        $display = isset($item['datetime2']) ? $item['datetime2'] : '';

        return g5b_seo_time_tag($dt, $display);
    }
}
