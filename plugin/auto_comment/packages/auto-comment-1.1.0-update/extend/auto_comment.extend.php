<?php
if (!defined('_GNUBOARD_')) exit;

$auto_comment_lib = G5_PLUGIN_PATH.'/auto_comment/auto_comment.lib.php';
if (is_file($auto_comment_lib)) {
    include_once($auto_comment_lib);
}

if (function_exists('add_event') && function_exists('auto_comment_schedule_for_post')) {
    add_event('write_update_after', 'auto_comment_schedule_for_post', 20, 3);
}

if (function_exists('add_event') && function_exists('auto_comment_maybe_run_worker')) {
    add_event('common_header', 'auto_comment_maybe_run_worker', 99, 0);
}

if (function_exists('add_event') && function_exists('auto_comment_maybe_sync_bot_points')) {
    add_event('common_header', 'auto_comment_maybe_sync_bot_points', 100, 0);
}

if (function_exists('add_event') && function_exists('auto_comment_track_post_view')) {
    add_event('tail_sub', 'auto_comment_track_post_view', 20, 0);
}

if (function_exists('add_replace')) {
    add_replace('admin_menu', 'auto_comment_admin_menu', 20, 1);
}

function auto_comment_admin_menu($admin_menu)
{
    if (defined('G5_PLUGIN_URL')) {
        $admin_menu['menu200'][] = array('200910', '자동댓글 관리', G5_PLUGIN_URL.'/auto_comment/admin/index.php', 'auto_comment');
    }

    return $admin_menu;
}
