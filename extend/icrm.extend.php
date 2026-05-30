<?php
/**
 * iCRM final_url 연동 — onoff-g5-base 내장
 *
 * - 글 저장 후 wr_seo_title 확정 (write_update_after)
 * - /icrm/final-url.php API (사이트별 G5_URL·토큰 자동)
 * - 별도 install 없음. 최초 접속 시 data/icrm.config.php 토큰 자동 생성 가능
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (is_file(G5_PATH.'/_site.config.php')) {
    include_once G5_PATH.'/_site.config.php';
}

if (function_exists('g5site_cfg_bool') && !g5site_cfg_bool('icrm_builtin', true)) {
    return;
}

if (!is_file(G5_LIB_PATH.'/icrm.lib.php')) {
    return;
}

include_once G5_LIB_PATH.'/icrm.lib.php';

if (function_exists('icrm_bootstrap')) {
    icrm_bootstrap();
}

if (!function_exists('icrm_on_write_update_after')) {
    function icrm_on_write_update_after($board, $wr_id, $w, $qstr, $redirect_url)
    {
        if (!is_array($board) || empty($board['bo_table']) || !(int) $wr_id) {
            return;
        }

        if (function_exists('icrm_ensure_wr_seo_title')) {
            icrm_ensure_wr_seo_title($board['bo_table'], (int) $wr_id);
        }
    }
}

if (!function_exists('icrm_on_common_header')) {
    function icrm_on_common_header()
    {
        if (function_exists('icrm_ensure_wr_seo_title_on_view')) {
            icrm_ensure_wr_seo_title_on_view();
        }
        if (function_exists('icrm_enqueue_board_assets')) {
            icrm_enqueue_board_assets();
        }
    }
}

if (function_exists('add_replace')) {
    add_replace('board_content_head', 'icrm_board_content_head_css', 5, 2);
    add_replace('board_mobile_content_head', 'icrm_board_content_head_css', 5, 2);
    add_replace('html_purifier_result', 'icrm_html_purifier_result', 10, 3);
}

if (function_exists('add_event')) {
    add_event('write_update_after', 'icrm_on_write_update_after', 10, 5);
    add_event('common_header', 'icrm_on_common_header', 5, 0);
    add_event('html_purifier_config', 'icrm_html_purifier_config', 10, 2);
}
