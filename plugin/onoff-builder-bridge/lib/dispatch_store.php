<?php
/**
 * CEBU 24 — phone + PIN dispatch store (JSON files, no DB)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('cebu24_dispatch_store_dir')) {
    function cebu24_dispatch_store_dir()
    {
        $dir = ONOFF_BUILDER_DATA_PATH . '/cebu24/dispatches';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $deny = ONOFF_BUILDER_DATA_PATH . '/cebu24/.htaccess';
        if (!is_file($deny)) {
            @file_put_contents(
                $deny,
                "Order allow,deny\nDeny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n"
            );
        }
        return $dir;
    }
}

if (!function_exists('cebu24_normalize_phone')) {
    function cebu24_normalize_phone($phone)
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === null) {
            return '';
        }
        // PH: 09XXXXXXXXX or 639XXXXXXXXX → 09XXXXXXXXX
        if (strlen($digits) === 12 && strpos($digits, '63') === 0) {
            $digits = '0' . substr($digits, 2);
        }
        if (strlen($digits) === 10 && $digits[0] === '9') {
            $digits = '0' . $digits;
        }
        return $digits;
    }
}

if (!function_exists('cebu24_dispatch_file_for_phone')) {
    function cebu24_dispatch_file_for_phone($phone_norm)
    {
        $hash = hash('sha256', 'cebu24|' . $phone_norm);
        return cebu24_dispatch_store_dir() . '/' . $hash . '.json';
    }
}

if (!function_exists('cebu24_load_phone_bucket')) {
    function cebu24_load_phone_bucket($phone_norm)
    {
        $path = cebu24_dispatch_file_for_phone($phone_norm);
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}

if (!function_exists('cebu24_save_phone_bucket')) {
    function cebu24_save_phone_bucket($phone_norm, $bucket)
    {
        $path = cebu24_dispatch_file_for_phone($phone_norm);
        $json = json_encode($bucket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }
        return @file_put_contents($path, $json, LOCK_EX) !== false;
    }
}

if (!function_exists('cebu24_sanitize_dispatch_item')) {
    function cebu24_sanitize_dispatch_item($item)
    {
        if (!is_array($item)) {
            return null;
        }
        unset($item['photos'], $item['driver'], $item['viewPin'], $item['pin']);
        $item['photos'] = array();
        return $item;
    }
}

if (!function_exists('cebu24_rate_limit_ok')) {
    function cebu24_rate_limit_ok($key, $max = 20, $window_sec = 600)
    {
        $dir = cebu24_dispatch_store_dir() . '/_rate';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/' . hash('sha256', $key) . '.json';
        $now = time();
        $hits = array();
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : null;
            if (is_array($data) && isset($data['hits']) && is_array($data['hits'])) {
                $hits = $data['hits'];
            }
        }
        $hits = array_values(array_filter($hits, function ($t) use ($now, $window_sec) {
            return is_numeric($t) && ($now - (int) $t) < $window_sec;
        }));
        if (count($hits) >= $max) {
            return false;
        }
        $hits[] = $now;
        @file_put_contents($file, json_encode(array('hits' => $hits)), LOCK_EX);
        return true;
    }
}
