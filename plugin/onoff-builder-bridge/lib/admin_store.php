<?php
/**
 * CEBU 24 administrator accounts and persistent login sessions.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('cebu24_admin_store_path')) {
    function cebu24_admin_store_path()
    {
        cebu24_dispatch_store_dir();
        return ONOFF_BUILDER_DATA_PATH . '/cebu24/admins.json';
    }
}

if (!function_exists('cebu24_admin_normalize_id')) {
    function cebu24_admin_normalize_id($id)
    {
        return strtolower(trim((string) $id));
    }
}

if (!function_exists('cebu24_admin_load_store')) {
    function cebu24_admin_load_store()
    {
        $path = cebu24_admin_store_path();
        $data = null;
        if (is_file($path)) {
            $raw = @file_get_contents($path);
            $data = $raw ? json_decode($raw, true) : null;
        }
        if (!is_array($data)) {
            $data = array('admins' => array(), 'sessions' => array());
        }
        if (empty($data['admins']) || !is_array($data['admins'])) {
            $data['admins'] = array();
        }
        if (empty($data['sessions']) || !is_array($data['sessions'])) {
            $data['sessions'] = array();
        }

        // Preserve the current owner access as the initial account.
        if (empty($data['admins']['owner'])) {
            $initial = getenv('CEBU24_INITIAL_ADMIN_PASSWORD');
            if (!is_string($initial) || $initial === '') {
                $initial = '8282';
            }
            $data['admins']['owner'] = array(
                'id' => 'owner',
                'name' => '대표 관리자',
                'role' => 'owner',
                'passwordHash' => password_hash($initial, PASSWORD_DEFAULT),
                'active' => true,
                'createdAt' => date('c'),
                'createdBy' => 'system',
            );
            cebu24_admin_save_store($data);
        }
        return $data;
    }
}

if (!function_exists('cebu24_admin_save_store')) {
    function cebu24_admin_save_store($data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }
        return @file_put_contents(cebu24_admin_store_path(), $json, LOCK_EX) !== false;
    }
}

if (!function_exists('cebu24_admin_public')) {
    function cebu24_admin_public($admin)
    {
        return array(
            'id' => isset($admin['id']) ? (string) $admin['id'] : '',
            'name' => isset($admin['name']) ? (string) $admin['name'] : '',
            'role' => isset($admin['role']) ? (string) $admin['role'] : 'admin',
            'createdAt' => isset($admin['createdAt']) ? (string) $admin['createdAt'] : '',
        );
    }
}

if (!function_exists('cebu24_admin_login')) {
    function cebu24_admin_login($id, $password)
    {
        $id = cebu24_admin_normalize_id($id);
        $store = cebu24_admin_load_store();
        if (empty($store['admins'][$id]) || empty($store['admins'][$id]['active'])) {
            return null;
        }
        $admin = $store['admins'][$id];
        if (empty($admin['passwordHash']) || !password_verify((string) $password, $admin['passwordHash'])) {
            return null;
        }
        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            return null;
        }
        $store['sessions'][hash('sha256', $token)] = array(
            'adminId' => $id,
            'createdAt' => date('c'),
            'lastSeenAt' => date('c'),
        );
        if (!cebu24_admin_save_store($store)) {
            return null;
        }
        return array('token' => $token, 'admin' => cebu24_admin_public($admin));
    }
}

if (!function_exists('cebu24_admin_from_token')) {
    function cebu24_admin_from_token($token, $touch = true)
    {
        $token = trim((string) $token);
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $key = hash('sha256', $token);
        $store = cebu24_admin_load_store();
        if (empty($store['sessions'][$key]['adminId'])) {
            return null;
        }
        $id = cebu24_admin_normalize_id($store['sessions'][$key]['adminId']);
        if (empty($store['admins'][$id]) || empty($store['admins'][$id]['active'])) {
            unset($store['sessions'][$key]);
            cebu24_admin_save_store($store);
            return null;
        }
        if ($touch) {
            $store['sessions'][$key]['lastSeenAt'] = date('c');
            cebu24_admin_save_store($store);
        }
        return cebu24_admin_public($store['admins'][$id]);
    }
}

if (!function_exists('cebu24_admin_logout')) {
    function cebu24_admin_logout($token)
    {
        $token = trim((string) $token);
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return false;
        }
        $store = cebu24_admin_load_store();
        unset($store['sessions'][hash('sha256', $token)]);
        return cebu24_admin_save_store($store);
    }
}

if (!function_exists('cebu24_admin_create')) {
    function cebu24_admin_create($id, $password, $name, $created_by)
    {
        $id = cebu24_admin_normalize_id($id);
        $name = trim((string) $name);
        $password = (string) $password;
        if (!preg_match('/^[a-z0-9_-]{3,24}$/', $id)) {
            return array('ok' => false, 'error' => 'invalid_admin_id');
        }
        if (strlen($password) < 6 || strlen($password) > 72) {
            return array('ok' => false, 'error' => 'invalid_admin_password');
        }
        if ($name === '' || strlen($name) > 40) {
            return array('ok' => false, 'error' => 'invalid_admin_name');
        }
        $store = cebu24_admin_load_store();
        if (!empty($store['admins'][$id])) {
            return array('ok' => false, 'error' => 'admin_exists');
        }
        $store['admins'][$id] = array(
            'id' => $id,
            'name' => $name,
            'role' => 'admin',
            'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
            'active' => true,
            'createdAt' => date('c'),
            'createdBy' => (string) $created_by,
        );
        $bootstrap_replaced = false;
        if ($created_by === 'owner' && !empty($store['admins']['owner']['active'])) {
            $store['admins']['owner']['active'] = false;
            foreach ($store['sessions'] as $session_key => $session) {
                if (isset($session['adminId']) && $session['adminId'] === 'owner') {
                    unset($store['sessions'][$session_key]);
                }
            }
            $bootstrap_replaced = true;
        }
        if (!cebu24_admin_save_store($store)) {
            return array('ok' => false, 'error' => 'save_failed');
        }
        return array(
            'ok' => true,
            'admin' => cebu24_admin_public($store['admins'][$id]),
            'bootstrapReplaced' => $bootstrap_replaced,
        );
    }
}

if (!function_exists('cebu24_admin_list_accounts')) {
    function cebu24_admin_list_accounts()
    {
        $store = cebu24_admin_load_store();
        $items = array();
        foreach ($store['admins'] as $admin) {
            if (!is_array($admin) || empty($admin['active'])) {
                continue;
            }
            $items[] = cebu24_admin_public($admin);
        }
        usort($items, function ($a, $b) {
            return strcmp(isset($a['createdAt']) ? $a['createdAt'] : '', isset($b['createdAt']) ? $b['createdAt'] : '');
        });
        return $items;
    }
}

if (!function_exists('cebu24_admin_delete')) {
    function cebu24_admin_delete($target_id, $deleted_by)
    {
        $target_id = cebu24_admin_normalize_id($target_id);
        $deleted_by = cebu24_admin_normalize_id($deleted_by);
        if ($target_id === '' || $target_id === $deleted_by) {
            return array('ok' => false, 'error' => 'cannot_delete_self');
        }

        $store = cebu24_admin_load_store();
        if (empty($store['admins'][$target_id]) || empty($store['admins'][$target_id]['active'])) {
            return array('ok' => false, 'error' => 'admin_not_found');
        }

        $active_count = 0;
        foreach ($store['admins'] as $admin) {
            if (is_array($admin) && !empty($admin['active'])) {
                $active_count++;
            }
        }
        if ($active_count <= 1) {
            return array('ok' => false, 'error' => 'last_admin');
        }

        $store['admins'][$target_id]['active'] = false;
        $store['admins'][$target_id]['deletedAt'] = date('c');
        $store['admins'][$target_id]['deletedBy'] = $deleted_by;
        foreach ($store['sessions'] as $session_key => $session) {
            if (isset($session['adminId']) && cebu24_admin_normalize_id($session['adminId']) === $target_id) {
                unset($store['sessions'][$session_key]);
            }
        }
        if (!cebu24_admin_save_store($store)) {
            return array('ok' => false, 'error' => 'save_failed');
        }
        return array('ok' => true);
    }
}

if (!function_exists('cebu24_admin_change_password')) {
    function cebu24_admin_change_password($admin_id, $current_password, $new_password)
    {
        $admin_id = cebu24_admin_normalize_id($admin_id);
        $new_password = (string) $new_password;
        if (strlen($new_password) < 6 || strlen($new_password) > 72) {
            return array('ok' => false, 'error' => 'invalid_admin_password');
        }

        $store = cebu24_admin_load_store();
        if (empty($store['admins'][$admin_id]) || empty($store['admins'][$admin_id]['active'])) {
            return array('ok' => false, 'error' => 'admin_not_found');
        }
        $admin = $store['admins'][$admin_id];
        if (empty($admin['passwordHash']) || !password_verify((string) $current_password, $admin['passwordHash'])) {
            return array('ok' => false, 'error' => 'invalid_current_password');
        }

        $store['admins'][$admin_id]['passwordHash'] = password_hash($new_password, PASSWORD_DEFAULT);
        $store['admins'][$admin_id]['passwordChangedAt'] = date('c');
        if (!cebu24_admin_save_store($store)) {
            return array('ok' => false, 'error' => 'save_failed');
        }
        return array('ok' => true);
    }
}

if (!function_exists('cebu24_admin_reset_password')) {
    function cebu24_admin_reset_password($target_id, $new_password, $reset_by)
    {
        $target_id = cebu24_admin_normalize_id($target_id);
        $reset_by = cebu24_admin_normalize_id($reset_by);
        $new_password = (string) $new_password;
        if ($target_id === $reset_by) {
            return array('ok' => false, 'error' => 'use_change_password');
        }
        if (strlen($new_password) < 6 || strlen($new_password) > 72) {
            return array('ok' => false, 'error' => 'invalid_admin_password');
        }

        $store = cebu24_admin_load_store();
        if (empty($store['admins'][$target_id]) || empty($store['admins'][$target_id]['active'])) {
            return array('ok' => false, 'error' => 'admin_not_found');
        }

        $store['admins'][$target_id]['passwordHash'] = password_hash($new_password, PASSWORD_DEFAULT);
        $store['admins'][$target_id]['passwordChangedAt'] = date('c');
        $store['admins'][$target_id]['passwordChangedBy'] = $reset_by;
        foreach ($store['sessions'] as $session_key => $session) {
            if (isset($session['adminId']) && cebu24_admin_normalize_id($session['adminId']) === $target_id) {
                unset($store['sessions'][$session_key]);
            }
        }
        if (!cebu24_admin_save_store($store)) {
            return array('ok' => false, 'error' => 'save_failed');
        }
        return array('ok' => true);
    }
}
