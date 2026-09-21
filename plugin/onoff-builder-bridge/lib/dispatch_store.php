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

if (!function_exists('cebu24_public_area')) {
    function cebu24_public_area($item)
    {
        $raw = '';
        if (!empty($item['landmark'])) {
            $raw = (string) $item['landmark'];
        } elseif (!empty($item['locationAddress'])) {
            $raw = (string) $item['locationAddress'];
        }
        $part = trim(explode(',', $raw)[0]);
        if ($part === '') {
            return 'Cebu';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($part, 0, 18);
        }
        return substr($part, 0, 18);
    }
}

if (!function_exists('cebu24_list_public_dispatches')) {
    /**
     * All recent requests, PII stripped. Optional phone+pin marks the caller's own rows.
     */
    function cebu24_list_public_dispatches($phone_norm = '', $pin = '')
    {
        $dir = cebu24_dispatch_store_dir();
        $mine_ids = array();
        if ($phone_norm !== '' && strlen($pin) === 4) {
            $bucket = cebu24_load_phone_bucket($phone_norm);
            if ($bucket && !empty($bucket['pin_hash']) && password_verify($pin, $bucket['pin_hash'])) {
                foreach ((isset($bucket['items']) && is_array($bucket['items'])) ? $bucket['items'] : array() as $own) {
                    if (is_array($own) && !empty($own['id'])) {
                        $mine_ids[(string) $own['id']] = true;
                    }
                }
            }
        }

        $rows = array();
        foreach (glob($dir . '/*.json') ?: array() as $file) {
            if (basename($file)[0] === '_') {
                continue;
            }
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : null;
            if (!is_array($data) || empty($data['items']) || !is_array($data['items'])) {
                continue;
            }
            foreach ($data['items'] as $item) {
                if (!is_array($item) || empty($item['id'])) {
                    continue;
                }
                $id = (string) $item['id'];
                $rows[] = array(
                    'id' => $id,
                    'serviceId' => isset($item['serviceId']) ? $item['serviceId'] : '',
                    'status' => isset($item['status']) ? $item['status'] : '',
                    'requestedAt' => isset($item['requestedAt']) ? $item['requestedAt'] : '',
                    'createdAtMs' => isset($item['createdAtMs']) ? (int) $item['createdAtMs'] : 0,
                    'area' => cebu24_public_area($item),
                    'mine' => isset($mine_ids[$id]),
                );
            }
        }

        usort($rows, function ($a, $b) {
            return ($b['createdAtMs'] ?? 0) <=> ($a['createdAtMs'] ?? 0);
        });
        return array_slice($rows, 0, 80);
    }
}

if (!function_exists('cebu24_admin_pin_ok')) {
    function cebu24_admin_pin_ok($pin)
    {
        return hash_equals('8282', (string) $pin);
    }
}

if (!function_exists('cebu24_admin_list_dispatches')) {
    function cebu24_admin_list_dispatches()
    {
        $dir = cebu24_dispatch_store_dir();
        $rows = array();
        foreach (glob($dir . '/*.json') ?: array() as $file) {
            if (basename($file)[0] === '_') {
                continue;
            }
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : null;
            if (!is_array($data) || empty($data['items']) || !is_array($data['items'])) {
                continue;
            }
            foreach ($data['items'] as $item) {
                if (!is_array($item) || empty($item['id'])) {
                    continue;
                }
                $clean = cebu24_sanitize_dispatch_item($item);
                if (!$clean) {
                    continue;
                }
                if (empty($clean['adminCallStatus'])) {
                    $clean['adminCallStatus'] = 'pending';
                }
                $rows[] = $clean;
            }
        }
        usort($rows, function ($a, $b) {
            return ($b['createdAtMs'] ?? 0) <=> ($a['createdAtMs'] ?? 0);
        });
        return array_slice($rows, 0, 80);
    }
}

if (!function_exists('cebu24_admin_set_status')) {
    function cebu24_admin_set_status($id, $status)
    {
        $allowed = array('pending', 'contacted', 'completed');
        if (!in_array($status, $allowed, true) || $id === '') {
            return false;
        }
        $dir = cebu24_dispatch_store_dir();
        foreach (glob($dir . '/*.json') ?: array() as $file) {
            if (basename($file)[0] === '_') {
                continue;
            }
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : null;
            if (!is_array($data) || empty($data['items']) || !is_array($data['items'])) {
                continue;
            }
            $changed = false;
            foreach ($data['items'] as $i => $item) {
                if (is_array($item) && isset($item['id']) && (string) $item['id'] === (string) $id) {
                    $data['items'][$i]['adminCallStatus'] = $status;
                    $changed = true;
                }
            }
            if ($changed) {
                $data['updated_at'] = date('c');
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return $json !== false && @file_put_contents($file, $json, LOCK_EX) !== false;
            }
        }
        return false;
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
