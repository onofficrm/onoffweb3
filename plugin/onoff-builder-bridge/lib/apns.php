<?php
/**
 * CEBU 24 APNs device registry and provider.
 *
 * Required server environment variables:
 * CEBU24_APNS_TEAM_ID, CEBU24_APNS_KEY_ID, CEBU24_APNS_PRIVATE_KEY_PATH
 * Optional: CEBU24_APNS_BUNDLE_ID (defaults to the iOS app bundle ID)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('cebu24_apns_store_path')) {
    function cebu24_apns_store_path()
    {
        cebu24_dispatch_store_dir();
        return ONOFF_BUILDER_DATA_PATH . '/cebu24/apns_devices.json';
    }
}

if (!function_exists('cebu24_apns_load_devices')) {
    function cebu24_apns_load_devices()
    {
        $path = cebu24_apns_store_path();
        $raw = is_file($path) ? @file_get_contents($path) : false;
        $data = $raw ? json_decode($raw, true) : null;
        return is_array($data) ? $data : array();
    }
}

if (!function_exists('cebu24_apns_save_devices')) {
    function cebu24_apns_save_devices($devices)
    {
        $json = json_encode($devices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json !== false
            && @file_put_contents(cebu24_apns_store_path(), $json, LOCK_EX) !== false;
    }
}

if (!function_exists('cebu24_apns_normalize_token')) {
    function cebu24_apns_normalize_token($token)
    {
        $token = strtolower(preg_replace('/[^a-fA-F0-9]/', '', (string) $token));
        return preg_match('/^[a-f0-9]{64,200}$/', $token) ? $token : '';
    }
}

if (!function_exists('cebu24_apns_register_device')) {
    function cebu24_apns_register_device($token, $environment, $bundle_id, $admin_id)
    {
        $token = cebu24_apns_normalize_token($token);
        $environment = $environment === 'sandbox' ? 'sandbox' : 'production';
        $expected_bundle = getenv('CEBU24_APNS_BUNDLE_ID') ?: 'net.iwinv.cebu24rescue';
        if ($token === '' || !hash_equals($expected_bundle, (string) $bundle_id)) {
            return false;
        }
        $devices = cebu24_apns_load_devices();
        $devices[hash('sha256', $token)] = array(
            'token' => $token,
            'environment' => $environment,
            'bundleId' => $expected_bundle,
            'adminId' => (string) $admin_id,
            'updatedAt' => date('c'),
        );
        return cebu24_apns_save_devices($devices);
    }
}

if (!function_exists('cebu24_apns_unregister_device')) {
    function cebu24_apns_unregister_device($token)
    {
        $token = cebu24_apns_normalize_token($token);
        if ($token === '') {
            return false;
        }
        $devices = cebu24_apns_load_devices();
        unset($devices[hash('sha256', $token)]);
        return cebu24_apns_save_devices($devices);
    }
}

if (!function_exists('cebu24_apns_base64url')) {
    function cebu24_apns_base64url($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

if (!function_exists('cebu24_apns_der_length')) {
    function cebu24_apns_der_length($der, &$offset)
    {
        if ($offset >= strlen($der)) {
            return -1;
        }
        $length = ord($der[$offset++]);
        if (($length & 0x80) === 0) {
            return $length;
        }
        $bytes = $length & 0x7f;
        if ($bytes < 1 || $bytes > 4 || $offset + $bytes > strlen($der)) {
            return -1;
        }
        $length = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $length = ($length << 8) | ord($der[$offset++]);
        }
        return $length;
    }
}

if (!function_exists('cebu24_apns_der_to_jose')) {
    function cebu24_apns_der_to_jose($der)
    {
        $offset = 0;
        if (strlen($der) < 8 || ord($der[$offset++]) !== 0x30) {
            return false;
        }
        if (cebu24_apns_der_length($der, $offset) < 0 || ord($der[$offset++]) !== 0x02) {
            return false;
        }
        $r_length = cebu24_apns_der_length($der, $offset);
        $r = substr($der, $offset, $r_length);
        $offset += $r_length;
        if ($offset >= strlen($der) || ord($der[$offset++]) !== 0x02) {
            return false;
        }
        $s_length = cebu24_apns_der_length($der, $offset);
        $s = substr($der, $offset, $s_length);
        $r = str_pad(substr(ltrim($r, "\x00"), -32), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(substr(ltrim($s, "\x00"), -32), 32, "\x00", STR_PAD_LEFT);
        return $r . $s;
    }
}

if (!function_exists('cebu24_apns_provider_token')) {
    function cebu24_apns_provider_token()
    {
        $team_id = trim((string) (getenv('CEBU24_APNS_TEAM_ID') ?: '394Y5YFY92'));
        $key_id = trim((string) (getenv('CEBU24_APNS_KEY_ID') ?: '6XZZCFQVY4'));
        $key_path = trim((string) getenv('CEBU24_APNS_PRIVATE_KEY_PATH'));
        if ($key_path === '') {
            $key_path = ONOFF_BUILDER_DATA_PATH . '/cebu24/private/AuthKey_6XZZCFQVY4.p8';
        }
        if ($team_id === '' || $key_id === '' || $key_path === '' || !is_readable($key_path)) {
            return null;
        }
        $private_key = openssl_pkey_get_private(@file_get_contents($key_path));
        if (!$private_key) {
            return null;
        }
        $header = cebu24_apns_base64url(json_encode(array('alg' => 'ES256', 'kid' => $key_id)));
        $claims = cebu24_apns_base64url(json_encode(array('iss' => $team_id, 'iat' => time())));
        $input = $header . '.' . $claims;
        $signature = '';
        if (!openssl_sign($input, $signature, $private_key, OPENSSL_ALGO_SHA256)) {
            return null;
        }
        $signature = cebu24_apns_der_to_jose($signature);
        return $signature === false ? null : $input . '.' . cebu24_apns_base64url($signature);
    }
}

if (!function_exists('cebu24_apns_send')) {
    function cebu24_apns_send($device, $payload, $provider_token)
    {
        if (!function_exists('curl_init')) {
            return array('ok' => false, 'status' => 0, 'reason' => 'curl_unavailable');
        }
        $host = $device['environment'] === 'sandbox'
            ? 'https://api.sandbox.push.apple.com'
            : 'https://api.push.apple.com';
        $url = $host . '/3/device/' . rawurlencode($device['token']);
        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_HTTPHEADER => array(
                'authorization: bearer ' . $provider_token,
                'apns-topic: ' . $device['bundleId'],
                'apns-push-type: alert',
                'apns-priority: 10',
                'content-type: application/json',
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
        ));
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        $decoded = $body ? json_decode($body, true) : null;
        return array(
            'ok' => $status === 200,
            'status' => $status,
            'reason' => is_array($decoded) && isset($decoded['reason']) ? $decoded['reason'] : '',
        );
    }
}

if (!function_exists('cebu24_apns_notify_new_dispatch')) {
    function cebu24_apns_notify_new_dispatch($item)
    {
        $devices = cebu24_apns_load_devices();
        if (empty($devices)) {
            return array('sent' => 0, 'failed' => 0);
        }
        $provider_token = cebu24_apns_provider_token();
        if ($provider_token === null) {
            error_log('CEBU24 APNs is not configured');
            return array('sent' => 0, 'failed' => count($devices));
        }
        $service = !empty($item['serviceName'])
            ? (string) $item['serviceName']
            : (!empty($item['serviceId']) ? (string) $item['serviceId'] : '긴급출동');
        $area = function_exists('cebu24_public_area') ? cebu24_public_area($item) : 'Cebu';
        $payload = array(
            'aps' => array(
                'alert' => array(
                    'title' => '새 긴급출동 접수',
                    'body' => $service . ' · ' . $area,
                ),
                'sound' => 'default',
                'badge' => function_exists('cebu24_launcher_badge_count')
                    ? cebu24_launcher_badge_count()
                    : 1,
            ),
            'dispatchId' => isset($item['id']) ? (string) $item['id'] : '',
            'url' => 'https://onoffweb3.iwinv.net/',
        );
        $sent = 0;
        $failed = 0;
        foreach ($devices as $key => $device) {
            if (!is_array($device) || empty($device['token']) || empty($device['bundleId'])) {
                unset($devices[$key]);
                continue;
            }
            $result = cebu24_apns_send($device, $payload, $provider_token);
            if (!empty($result['ok'])) {
                $sent++;
                continue;
            }
            $failed++;
            if ($result['status'] === 410 || in_array($result['reason'], array(
                'BadDeviceToken',
                'DeviceTokenNotForTopic',
                'Unregistered',
            ), true)) {
                unset($devices[$key]);
            }
        }
        cebu24_apns_save_devices($devices);
        return array('sent' => $sent, 'failed' => $failed);
    }
}
