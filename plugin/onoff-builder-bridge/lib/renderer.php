<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_builder_get_import_html')) {
    function onoff_builder_get_import_html($id)
    {
        $meta = onoff_builder_get_import($id);
        if (!$meta) {
            return '';
        }

        $dir = onoff_builder_import_dir($id);
        $entry = isset($meta['entry_file']) ? $meta['entry_file'] : 'index.html';
        if (!$dir || !onoff_builder_safe_join_path($dir, $entry)) {
            return '';
        }

        $file = onoff_builder_safe_join_path($dir, $entry);
        if (!$file || !is_file($file)) {
            return '';
        }

        $html = @file_get_contents($file);
        if ($html === false) {
            return '';
        }

        $import_url = onoff_builder_import_url($id);
        if (isset($meta['import_path']) && $meta['import_path'] !== '') {
            $base = rtrim((string) $meta['import_path'], '/');
            if (defined('G5_URL') && strpos($base, G5_URL) === 0) {
                $import_url = $base;
            }
        }

        return onoff_builder_rewrite_asset_paths($html, $import_url);
    }
}

if (!function_exists('onoff_builder_rewrite_asset_paths')) {
    function onoff_builder_rewrite_asset_paths($html, $import_url)
    {
        $base = rtrim((string) $import_url, '/');
        if ($base === '') {
            return $html;
        }

        $patterns = array(
            '#\ssrc=(["\'])/assets/#i' => ' src=$1' . $base . '/assets/',
            '#\shref=(["\'])/assets/#i' => ' href=$1' . $base . '/assets/',
            '#\ssrc=(["\'])assets/#i' => ' src=$1' . $base . '/assets/',
            '#\shref=(["\'])assets/#i' => ' href=$1' . $base . '/assets/',
            '#\ssrc=(["\'])\./assets/#i' => ' src=$1' . $base . '/assets/',
            '#\shref=(["\'])\./assets/#i' => ' href=$1' . $base . '/assets/',
            '#\ssrc=(["\'])/favicon#i' => ' src=$1' . $base . '/favicon',
            '#\shref=(["\'])/favicon#i' => ' href=$1' . $base . '/favicon',
        );

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }

        return $html;
    }
}

if (!function_exists('onoff_builder_extract_head_assets')) {
    function onoff_builder_extract_head_assets($html)
    {
        $out = '';
        if (preg_match_all('#<link[^>]+>#i', $html, $m)) {
            $out .= implode("\n", $m[0]) . "\n";
        }
        if (preg_match_all('#<style[^>]*>.*?</style>#is', $html, $m)) {
            $out .= implode("\n", $m[0]) . "\n";
        }
        if (preg_match_all('#<script[^>]+></script>#i', $html, $m)) {
            $out .= implode("\n", $m[0]) . "\n";
        }

        return $out;
    }
}

if (!function_exists('onoff_builder_extract_body_content')) {
    function onoff_builder_extract_body_content($html)
    {
        if (preg_match('#<body[^>]*>(.*)</body>#is', $html, $m)) {
            return $m[1];
        }

        return $html;
    }
}

if (!function_exists('onoff_builder_render_standalone')) {
    function onoff_builder_render_standalone($id, $options = array())
    {
        $meta = onoff_builder_get_import($id);
        if (!$meta) {
            onoff_builder_render_error('프로젝트를 찾을 수 없습니다.');
            return;
        }

        $html = onoff_builder_get_import_html($id);
        if ($html === '') {
            onoff_builder_render_error('HTML을 불러올 수 없습니다.');
            return;
        }

        $title = isset($meta['seo_title']) && $meta['seo_title'] !== ''
            ? $meta['seo_title']
            : (isset($meta['name']) ? $meta['name'] : $id);
        $desc = isset($meta['seo_description']) ? $meta['seo_description'] : '';

        if (!empty($options['preview_bar'])) {
            echo '<div class="onoff-builder-preview-bar">';
            echo '<strong>관리자 미리보기</strong> — ' . onoff_builder_escape($id);
            echo ' <a href="' . onoff_builder_escape(onoff_builder_page_url($id)) . '">공개 URL</a>';
            echo ' <a href="' . onoff_builder_escape(onoff_builder_admin_url('import-list.php')) . '">관리</a>';
            echo '</div>';
        }

        if (preg_match('#<!DOCTYPE#i', $html) || preg_match('#<html#i', $html)) {
            if ($desc !== '' && stripos($html, '<meta name="description"') === false) {
                $html = preg_replace(
                    '#</head>#i',
                    '<meta name="description" content="' . onoff_builder_escape($desc) . '"></head>',
                    $html,
                    1
                );
            }
            if (stripos($html, '<title>') !== false && $title !== '') {
                $html = preg_replace('#<title>.*?</title>#is', '<title>' . onoff_builder_escape($title) . '</title>', $html, 1);
            }
            echo $html;
            return;
        }

        echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . onoff_builder_escape($title) . '</title>';
        if ($desc !== '') {
            echo '<meta name="description" content="' . onoff_builder_escape($desc) . '">';
        }
        echo onoff_builder_extract_head_assets($html);
        echo '<link rel="stylesheet" href="' . onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/css/frontend.css') . '">';
        echo '</head><body class="onoff-builder-standalone">';
        echo onoff_builder_extract_body_content($html);
        echo '</body></html>';
    }
}

if (!function_exists('onoff_builder_render_with_gnuboard_layout')) {
    function onoff_builder_render_with_gnuboard_layout($id)
    {
        $meta = onoff_builder_get_import($id);
        if (!$meta) {
            onoff_builder_render_error('프로젝트를 찾을 수 없습니다.');
            return;
        }

        $g5['title'] = isset($meta['seo_title']) && $meta['seo_title'] !== ''
            ? $meta['seo_title']
            : $meta['name'];

        include_once G5_PATH . '/head.php';

        echo '<div class="onoff-builder-gnuboard-wrap">';
        echo onoff_builder_extract_body_content(onoff_builder_get_import_html($id));
        echo '</div>';

        include_once G5_PATH . '/tail.php';
    }
}

if (!function_exists('onoff_builder_render_error')) {
    function onoff_builder_render_error($message)
    {
        echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>페이지 안내</title>';
        echo '<link rel="stylesheet" href="' . onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/css/frontend.css') . '">';
        echo '</head><body class="onoff-builder-error-page">';
        echo '<div class="onoff-builder-error-box"><h1>안내</h1><p>' . onoff_builder_escape($message) . '</p></div>';
        echo '</body></html>';
    }
}

if (!function_exists('onoff_builder_render_import_page')) {
    function onoff_builder_render_import_page($id, $force_preview = false)
    {
        $meta = onoff_builder_get_import($id);
        if (!$meta) {
            onoff_builder_render_error('프로젝트를 찾을 수 없습니다.');
            return;
        }

        if (!$force_preview && empty($meta['enabled'])) {
            onoff_builder_render_error('이 페이지는 현재 비활성화되어 있습니다.');
            return;
        }

        $mode = isset($meta['mode']) ? $meta['mode'] : 'standalone';

        if ($mode === 'gnuboard-layout') {
            onoff_builder_render_with_gnuboard_layout($id);
            return;
        }

        onoff_builder_render_standalone($id);
    }
}
