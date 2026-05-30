<?php
/**
 * 관련글 목록 (게시글·페이지 하단)
 *
 * $related_bo_table   (필수) 게시판 ID
 * $related_keyword    (선택) 제목·내용 검색어 — 결과 없으면 최신글 fallback
 * $related_limit      (선택) 기본 4
 * $related_title      (선택) 기본 '관련 글'
 * $related_exclude_wr_id (선택) 현재 글 ID 제외
 * $related_layout     (선택) card | list — 기본 card
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/content-posts-helper.php';

$related_bo_table = isset($related_bo_table) ? g5b_content_sanitize_bo_table($related_bo_table) : '';
if ($related_bo_table === '') {
    return;
}

if (!g5b_content_board_available($related_bo_table)) {
    return;
}

$related_limit = isset($related_limit) ? max(1, min(12, (int) $related_limit)) : 4;
$related_title = isset($related_title) ? trim((string) $related_title) : '관련 글';
$related_keyword = isset($related_keyword) ? trim(strip_tags((string) $related_keyword)) : '';
$related_exclude_wr_id = isset($related_exclude_wr_id) ? (int) $related_exclude_wr_id : 0;
if ($related_exclude_wr_id < 1 && isset($view['wr_id'])) {
    $related_exclude_wr_id = (int) $view['wr_id'];
}
$related_layout = (isset($related_layout) && $related_layout === 'list') ? 'list' : 'card';

$fetch_args = array(
    'exclude_wr_id' => $related_exclude_wr_id,
);

$posts = array();

if ($related_keyword !== '') {
    $fetch_args['keyword'] = $related_keyword;
    $posts = g5b_content_fetch_posts($related_bo_table, $related_limit, $fetch_args);
}

if (empty($posts)) {
    unset($fetch_args['keyword']);
    $posts = g5b_content_fetch_posts($related_bo_table, $related_limit, $fetch_args);
}

if (empty($posts)) {
    return;
}

g5b_content_render_posts('related-posts', $related_title, $posts, $related_layout, $related_bo_table);
