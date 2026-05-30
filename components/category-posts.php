<?php
/**
 * 게시판 분류(카테고리) 글 목록
 *
 * $category_bo_table  (필수) 게시판 ID
 * $category_name      (선택) 분류명 — 비우면 해당 게시판 최신글 fallback
 * $category_limit     (선택) 기본 5
 * $category_title     (선택) 섹션 제목
 * $category_skin_type (선택) card | list — 기본 card
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/content-posts-helper.php';

$category_bo_table = isset($category_bo_table) ? g5b_content_sanitize_bo_table($category_bo_table) : '';
if ($category_bo_table === '' || !g5b_content_board_available($category_bo_table)) {
    return;
}

$category_limit = isset($category_limit) ? max(1, min(20, (int) $category_limit)) : 5;
$category_title = isset($category_title) ? trim((string) $category_title) : '분류 글';
$category_name = isset($category_name) ? trim(strip_tags((string) $category_name)) : '';
$category_skin_type = (isset($category_skin_type) && $category_skin_type === 'list') ? 'list' : 'card';

$fetch_args = array();
if ($category_name !== '') {
    $fetch_args['ca_name'] = $category_name;
    $category_title_suffix = $category_name;
    if ($category_title === '분류 글') {
        $category_title = $category_name;
    }
}

$posts = g5b_content_fetch_posts($category_bo_table, $category_limit, $fetch_args);

if (empty($posts)) {
    return;
}

g5b_content_render_posts('category-posts', $category_title, $posts, $category_skin_type, $category_bo_table);
