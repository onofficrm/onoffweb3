<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 최고관리자 여부 (ZIP 업로드·삭제)
 */
if (!function_exists('onoff_builder_is_admin')) {
    function onoff_builder_is_admin()
    {
        global $is_admin;

        return $is_admin === 'super';
    }
}

if (!function_exists('onoff_builder_require_admin')) {
    function onoff_builder_require_admin($redirect_url = '')
    {
        if (onoff_builder_is_admin()) {
            return;
        }

        if ($redirect_url === '') {
            $redirect_url = defined('G5_URL') ? G5_URL : '/';
        }

        onoff_builder_alert('최고관리자만 접근할 수 있습니다.', $redirect_url);
    }
}

if (!function_exists('onoff_builder_sanitize_project_id')) {
    function onoff_builder_sanitize_project_id($id)
    {
        $id = strtolower(trim((string) $id));
        $id = preg_replace('/[^a-z0-9_-]/', '', $id);

        return $id;
    }
}

if (!function_exists('onoff_builder_validate_project_id')) {
    function onoff_builder_validate_project_id($id)
    {
        $raw = trim((string) $id);

        if ($raw === '' || preg_match('/[^a-zA-Z0-9_-]/', $raw)) {
            return false;
        }

        if (stripos($raw, '..') !== false) {
            return false;
        }

        $id = onoff_builder_sanitize_project_id($raw);

        if ($id === '' || strlen($id) < 2 || strlen($id) > 50) {
            return false;
        }

        return true;
    }
}

if (!function_exists('onoff_builder_escape')) {
    function onoff_builder_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('onoff_builder_safe_join_path')) {
    function onoff_builder_safe_join_path($base, $path)
    {
        $base = rtrim(str_replace('\\', '/', $base), '/');
        $path = str_replace('\\', '/', (string) $path);
        $path = ltrim($path, '/');

        if ($path === '' || strpos($path, '..') !== false || $path[0] === '/') {
            return false;
        }

        $full = $base . '/' . $path;
        $base_real = realpath($base);
        $dir_real = realpath(dirname($full));

        if ($base_real === false) {
            if (!onoff_builder_ensure_dir($base)) {
                return false;
            }
            $base_real = realpath($base);
        }

        if ($base_real === false || $dir_real === false) {
            return false;
        }

        if (strpos($dir_real, $base_real) !== 0) {
            return false;
        }

        return $full;
    }
}

if (!function_exists('onoff_builder_is_safe_zip_entry')) {
    function onoff_builder_is_safe_zip_entry($entry)
    {
        $entry = str_replace('\\', '/', (string) $entry);

        if ($entry === '' || $entry[0] === '/' || preg_match('#^[a-zA-Z]:/#', $entry)) {
            return false;
        }

        if (strpos($entry, '../') !== false || strpos($entry, '/..') !== false || $entry === '..') {
            return false;
        }

        return true;
    }
}

if (!function_exists('onoff_builder_is_blocked_file')) {
    function onoff_builder_is_blocked_file($filename)
    {
        $name = strtolower(basename(str_replace('\\', '/', $filename)));

        if ($name === '' || $name === '.' || $name === '..') {
            return true;
        }

        $blocked_names = array(
            '.htaccess',
            'web.config',
            '.env',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
            'yarn.lock',
            'pnpm-lock.yaml',
        );

        if (in_array($name, $blocked_names, true)) {
            return true;
        }

        $blocked_ext = array('php', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'sh', 'bat', 'cmd');
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        if ($ext !== '' && in_array($ext, $blocked_ext, true)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('onoff_builder_is_blocked_path')) {
    function onoff_builder_is_blocked_path($path)
    {
        $path = strtolower(str_replace('\\', '/', $path));
        $parts = explode('/', $path);

        foreach ($parts as $part) {
            if ($part === 'node_modules' || $part === '.git' || $part === 'vendor') {
                return true;
            }
        }

        $base = basename($path);

        return onoff_builder_is_blocked_file($base);
    }
}

if (!function_exists('onoff_builder_require_post')) {
    function onoff_builder_require_post()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            onoff_builder_alert('잘못된 요청입니다.');
        }
    }
}
