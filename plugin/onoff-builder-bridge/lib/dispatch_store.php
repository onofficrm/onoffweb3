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
    function cebu24_sanitize_dispatch_item($item, $include_photos = false)
    {
        if (!is_array($item)) {
            return null;
        }
        $photos = array();
        if ($include_photos && !empty($item['photos']) && is_array($item['photos'])) {
            foreach (array_slice($item['photos'], 0, 10) as $photo_id) {
                $photo_id = basename((string) $photo_id);
                if (preg_match('/^[a-f0-9]{48}\.(?:jpe?g|png|webp|heic|heif)$/', $photo_id)) {
                    $photos[] = $photo_id;
                }
            }
        }
        unset($item['photos'], $item['driver'], $item['viewPin'], $item['pin']);
        $item['photos'] = $photos;
        return $item;
    }
}

if (!function_exists('cebu24_photo_store_dir')) {
    function cebu24_photo_store_dir()
    {
        $dir = ONOFF_BUILDER_DATA_PATH . '/cebu24/photos';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $deny = $dir . '/.htaccess';
        if (!is_file($deny)) {
            @file_put_contents(
                $deny,
                "Options -Indexes\n"
                . "<FilesMatch \"\\.(php|phtml|phar|cgi|pl|py|sh|htm|html|js)$\">\n"
                . "  Order allow,deny\n"
                . "  Deny from all\n"
                . "  <IfModule mod_authz_core.c>\n"
                . "    Require all denied\n"
                . "  </IfModule>\n"
                . "</FilesMatch>\n"
            );
        }
        return $dir;
    }
}

if (!function_exists('cebu24_photo_path')) {
    function cebu24_photo_path($photo_id)
    {
        $photo_id = basename((string) $photo_id);
        if (!preg_match('/^[a-f0-9]{48}\.(?:jpe?g|png|webp|heic|heif)$/', $photo_id)) {
            return null;
        }
        return cebu24_photo_store_dir() . '/' . $photo_id;
    }
}

if (!function_exists('cebu24_photo_mime')) {
    function cebu24_photo_mime($path, $original_name = '')
    {
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string) @finfo_file($finfo, $path);
                @finfo_close($finfo);
            }
        }
        $allowed = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
        );
        if (isset($allowed[$mime])) {
            return array('mime' => $mime, 'extension' => $allowed[$mime]);
        }

        $extension = strtolower(pathinfo((string) $original_name, PATHINFO_EXTENSION));
        if (in_array($extension, array('heic', 'heif'), true)) {
            $header = @file_get_contents($path, false, null, 0, 32);
            if (is_string($header) && preg_match('/ftyp(?:heic|heix|hevc|hevx|mif1|msf1)/', $header)) {
                return array(
                    'mime' => $extension === 'heif' ? 'image/heif' : 'image/heic',
                    'extension' => $extension,
                );
            }
        }
        return null;
    }
}

if (!function_exists('cebu24_photo_normalize_uploads')) {
    function cebu24_photo_normalize_uploads($field)
    {
        if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
            return array();
        }
        $raw = $_FILES[$field];
        $names = isset($raw['name']) && is_array($raw['name']) ? $raw['name'] : array($raw['name'] ?? '');
        $tmp_names = isset($raw['tmp_name']) && is_array($raw['tmp_name']) ? $raw['tmp_name'] : array($raw['tmp_name'] ?? '');
        $errors = isset($raw['error']) && is_array($raw['error']) ? $raw['error'] : array($raw['error'] ?? UPLOAD_ERR_NO_FILE);
        $sizes = isset($raw['size']) && is_array($raw['size']) ? $raw['size'] : array($raw['size'] ?? 0);
        $items = array();
        foreach ($names as $index => $name) {
            $items[] = array(
                'name' => (string) $name,
                'tmp_name' => isset($tmp_names[$index]) ? (string) $tmp_names[$index] : '',
                'error' => isset($errors[$index]) ? (int) $errors[$index] : UPLOAD_ERR_NO_FILE,
                'size' => isset($sizes[$index]) ? (int) $sizes[$index] : 0,
            );
        }
        return $items;
    }
}

if (!function_exists('cebu24_photo_store_uploads')) {
    function cebu24_photo_store_uploads($field = 'photos')
    {
        $uploads = cebu24_photo_normalize_uploads($field);
        if (count($uploads) > 10) {
            return array('ok' => false, 'error' => 'too_many_photos');
        }

        $validated = array();
        foreach ($uploads as $upload) {
            if ($upload['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($upload['error'] !== UPLOAD_ERR_OK) {
                return array('ok' => false, 'error' => 'photo_upload_failed');
            }
            if ($upload['size'] < 1 || $upload['size'] > 8 * 1024 * 1024) {
                return array('ok' => false, 'error' => 'photo_too_large');
            }
            if ($upload['tmp_name'] === '' || !is_uploaded_file($upload['tmp_name'])) {
                return array('ok' => false, 'error' => 'invalid_photo_upload');
            }
            $detected = cebu24_photo_mime($upload['tmp_name'], $upload['name']);
            if (!$detected) {
                return array('ok' => false, 'error' => 'unsupported_photo_type');
            }
            $validated[] = array_merge($upload, $detected);
        }

        $saved = array();
        foreach ($validated as $upload) {
            try {
                $photo_id = bin2hex(random_bytes(24)) . '.' . $upload['extension'];
            } catch (Exception $e) {
                cebu24_photo_delete_many($saved);
                return array('ok' => false, 'error' => 'photo_name_failed');
            }
            $path = cebu24_photo_path($photo_id);
            if (!$path || !@move_uploaded_file($upload['tmp_name'], $path)) {
                cebu24_photo_delete_many($saved);
                return array('ok' => false, 'error' => 'photo_save_failed');
            }
            @chmod($path, 0644);
            $saved[] = $photo_id;
        }
        return array('ok' => true, 'photos' => $saved);
    }
}

if (!function_exists('cebu24_photo_delete_many')) {
    function cebu24_photo_delete_many($photo_ids)
    {
        if (!is_array($photo_ids)) {
            return;
        }
        foreach ($photo_ids as $photo_id) {
            $path = cebu24_photo_path($photo_id);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }
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
                $receipt = '';
                if (!empty($item['receiptNo']) && preg_match('/^\d{4}$/', (string) $item['receiptNo'])) {
                    $receipt = (string) $item['receiptNo'];
                } elseif (!empty($item['contactPhone'])) {
                    $phone_digits = preg_replace('/\D+/', '', (string) $item['contactPhone']);
                    if (is_string($phone_digits) && strlen($phone_digits) >= 4) {
                        $receipt = substr($phone_digits, -4);
                    }
                }
                if ($receipt === '') {
                    $receipt = '----';
                }
                $rows[] = array(
                    'id' => $id,
                    'receiptNo' => $receipt,
                    'serviceId' => isset($item['serviceId']) ? $item['serviceId'] : '',
                    'status' => isset($item['status']) ? $item['status'] : '',
                    'requestedAt' => isset($item['requestedAt']) ? $item['requestedAt'] : '',
                    'createdAtMs' => isset($item['createdAtMs']) ? (int) $item['createdAtMs'] : 0,
                    'updatedAtMs' => cebu24_item_activity_ms($item),
                    'pending' => cebu24_item_is_pending($item),
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
                $clean = cebu24_sanitize_dispatch_item($item, true);
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
        return array_slice($rows, 0, 1000);
    }
}

if (!function_exists('cebu24_admin_set_status')) {
    function cebu24_admin_set_status($id, $status, $handled_by = '')
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
                    $data['items'][$i]['statusUpdatedAt'] = (int) round(microtime(true) * 1000);
                    if ($status === 'pending') {
                        unset($data['items'][$i]['handledBy']);
                    } elseif ($handled_by !== '') {
                        $data['items'][$i]['handledBy'] = (string) $handled_by;
                    }
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

if (!function_exists('cebu24_item_activity_ms')) {
    function cebu24_item_activity_ms($item)
    {
        $updated = isset($item['statusUpdatedAt']) ? (int) $item['statusUpdatedAt'] : 0;
        $created = isset($item['createdAtMs']) ? (int) $item['createdAtMs'] : 0;
        return max($updated, $created);
    }
}

if (!function_exists('cebu24_item_is_pending')) {
    function cebu24_item_is_pending($item)
    {
        $status = isset($item['status']) ? (string) $item['status'] : '';
        if (in_array($status, array('completed', 'cancelled'), true)) {
            return false;
        }
        $call = isset($item['adminCallStatus']) ? (string) $item['adminCallStatus'] : 'pending';
        return !in_array($call, array('contacted', 'completed'), true);
    }
}

if (!function_exists('cebu24_each_dispatch_item')) {
    function cebu24_each_dispatch_item($callback)
    {
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
            foreach ($data['items'] as $item) {
                if (is_array($item) && !empty($item['id'])) {
                    $callback($item);
                }
            }
        }
    }
}

if (!function_exists('cebu24_launcher_badge_count')) {
    /**
     * Unhandled customer requests. Zero once the admin marks one in progress or done.
     */
    function cebu24_launcher_badge_count()
    {
        $pending = 0;
        cebu24_each_dispatch_item(function ($item) use (&$pending) {
            if (cebu24_item_is_pending($item)) {
                $pending++;
            }
        });
        return $pending > 0 ? min($pending, 99) : 0;
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
