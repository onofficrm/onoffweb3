<?php
/**
 * 게시판 최신글 (메인·서브·사이드)
 *
 * $latest_bo_table   (필수) 게시판 ID
 * $latest_limit      (선택) 기본 5
 * $latest_title       (선택) 섹션 제목
 * $latest_skin_type   (선택) card | list — 기본 card (latest() 스킨과 별개 마크업)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/content-posts-helper.php';

$latest_bo_table = isset($latest_bo_table) ? g5b_content_sanitize_bo_table($latest_bo_table) : '';
if ($latest_bo_table === '' || !g5b_content_board_available($latest_bo_table)) {
    return;
}

$latest_limit = isset($latest_limit) ? max(1, min(20, (int) $latest_limit)) : 5;
$latest_title = isset($latest_title) ? trim((string) $latest_title) : '최신 글';
$latest_skin_type = (isset($latest_skin_type) && $latest_skin_type === 'list') ? 'list' : 'card';

$posts = g5b_content_fetch_posts($latest_bo_table, $latest_limit, array());

if (empty($posts)) {
    return;
}

g5b_content_render_posts('latest-posts', $latest_title, $posts, $latest_skin_type, $latest_bo_table);
