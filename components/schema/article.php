<?php
/**
 * Article JSON-LD (게시글·칼럼·뉴스)
 *
 * 페이지 변수 (선택):
 * - $article_title, $article_description, $article_url, $article_image
 * - $article_date_published, $article_date_modified (ISO 8601 권장)
 * - $article_author_name
 *
 * 게시판 view에서 $view 배열이 있으면 일부 자동 매핑
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once dirname(__FILE__) . '/_helpers.php';

g5b_schema_load_config();

$title = '';
$description = '';
$url = '';
$image = '';
$date_published = '';
$date_modified = '';
$author_name = '';

if (!empty($article_title)) {
    $title = g5b_schema_clean_text($article_title);
}
if (!empty($article_description)) {
    $description = g5b_schema_clean_text($article_description);
}
if (!empty($article_url)) {
    $url = g5b_schema_abs_url($article_url);
}
if (!empty($article_image)) {
    $image = g5b_schema_abs_url($article_image);
}
if (!empty($article_date_published)) {
    $date_published = g5b_schema_clean_text($article_date_published);
}
if (!empty($article_date_modified)) {
    $date_modified = g5b_schema_clean_text($article_date_modified);
}
if (!empty($article_author_name)) {
    $author_name = g5b_schema_clean_text($article_author_name);
}

if ($title === '' && isset($view) && is_array($view)) {
    if (!empty($view['wr_subject'])) {
        $title = g5b_schema_clean_text($view['wr_subject']);
    }
    if ($description === '' && !empty($view['wr_content'])) {
        $plain = preg_replace('/\s+/', ' ', strip_tags($view['wr_content']));
        $description = g5b_schema_clean_text(function_exists('cut_str') ? cut_str($plain, 200) : substr($plain, 0, 200));
    }
    if ($url === '' && !empty($view['wr_id']) && isset($bo_table)) {
        $bo_table_safe = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table_safe !== '' && function_exists('get_pretty_url')) {
            $url = g5b_schema_abs_url(get_pretty_url($bo_table_safe, $view['wr_id']));
        }
    }
    if ($date_published === '' && !empty($view['wr_datetime'])) {
        $date_published = date('c', strtotime($view['wr_datetime']));
    }
    if ($date_modified === '' && !empty($view['wr_last'])) {
        $date_modified = date('c', strtotime($view['wr_last']));
    }
    if ($author_name === '' && !empty($view['wr_name'])) {
        $author_name = g5b_schema_clean_text($view['wr_name']);
    }
}

if ($title === '' || $url === '') {
    return;
}

$site_url = g5b_schema_site_url();
$publisher_name = g5b_schema_cfg('company_name', g5b_schema_cfg('site_name', ''));

$schema = array(
    '@context'        => 'https://schema.org',
    '@type'           => 'Article',
    'headline'        => $title,
    'mainEntityOfPage'=> array(
        '@type' => 'WebPage',
        '@id'   => $url,
    ),
    'url'             => $url,
);

if ($description !== '') {
    $schema['description'] = $description;
}
if ($image !== '') {
    $schema['image'] = array($image);
}
if ($date_published !== '') {
    $schema['datePublished'] = $date_published;
}
if ($date_modified !== '') {
    $schema['dateModified'] = $date_modified;
}

$author = array('@type' => 'Person');
if ($author_name !== '') {
    $author['name'] = $author_name;
} else {
    $author['name'] = $publisher_name !== '' ? $publisher_name : 'Author';
}
$schema['author'] = $author;

if ($publisher_name !== '' && $site_url !== '') {
    $publisher = array(
        '@type' => 'Organization',
        'name'  => $publisher_name,
        'url'   => $site_url,
    );
    $logo = g5b_schema_cfg_url('logo_path', '');
    if ($logo !== '') {
        $publisher['logo'] = array(
            '@type' => 'ImageObject',
            'url'   => $logo,
        );
    }
    $schema['publisher'] = $publisher;
}

g5b_schema_print_jsonld($schema);
