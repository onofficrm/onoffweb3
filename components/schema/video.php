<?php
/**
 * VideoObject JSON-LD (유튜브 게시판 글보기)
 *
 * 필수 변수:
 * - $video_schema_title
 * - $video_schema_id (11자 영상 ID)
 *
 * 선택 변수:
 * - $video_schema_description
 * - $video_schema_thumbnail
 * - $video_schema_upload_date (ISO 8601)
 * - $video_schema_embed_url
 * - $video_schema_content_url
 *
 * publisher 등은 필요 시 추후 확장
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/_helpers.php';

$title = isset($video_schema_title) ? g5b_schema_clean_text($video_schema_title) : '';
$video_id = isset($video_schema_id) ? trim((string) $video_schema_id) : '';

if ($title === '' || $video_id === '') {
    return;
}

if (function_exists('g5b_youtube_sanitize_id')) {
    $video_id = g5b_youtube_sanitize_id($video_id);
} elseif (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) {
    return;
}

if ($video_id === '') {
    return;
}

$description = isset($video_schema_description) ? g5b_schema_clean_text($video_schema_description) : '';
if ($description !== '' && function_exists('cut_str')) {
    $description = cut_str($description, 200, '…');
} elseif ($description !== '' && strlen($description) > 200) {
    $description = cut_str($description, 200, '…');
}

$thumbnail = isset($video_schema_thumbnail) ? trim((string) $video_schema_thumbnail) : '';
if ($thumbnail === '' && function_exists('g5b_youtube_thumb_url')) {
    $thumbnail = g5b_youtube_thumb_url($video_id);
}
$thumbnail = g5b_schema_abs_url($thumbnail);

$upload_date = isset($video_schema_upload_date) ? trim((string) $video_schema_upload_date) : '';

$embed_url = isset($video_schema_embed_url) ? trim((string) $video_schema_embed_url) : '';
if ($embed_url === '' && function_exists('g5b_youtube_schema_embed_url')) {
    $embed_url = g5b_youtube_schema_embed_url($video_id);
}

$content_url = isset($video_schema_content_url) ? trim((string) $video_schema_content_url) : '';
if ($content_url === '' && function_exists('g5b_youtube_watch_url')) {
    $content_url = g5b_youtube_watch_url($video_id);
}

$embed_url = g5b_schema_abs_url($embed_url);
$content_url = g5b_schema_abs_url($content_url);

if ($embed_url === '' || $content_url === '') {
    return;
}

$schema = array(
    '@context'    => 'https://schema.org',
    '@type'       => 'VideoObject',
    'name'        => $title,
    'thumbnailUrl'=> $thumbnail !== '' ? $thumbnail : 'https://img.youtube.com/vi/'.$video_id.'/hqdefault.jpg',
    'embedUrl'    => $embed_url,
    'contentUrl'  => $content_url,
);

if ($description !== '') {
    $schema['description'] = $description;
}

if ($upload_date !== '') {
    $schema['uploadDate'] = $upload_date;
}

g5b_schema_print_jsonld($schema);
