<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('onoff_builder_meta_path')) {
    function onoff_builder_meta_path($id)
    {
        if (!onoff_builder_validate_project_id($id)) {
            return false;
        }

        return ONOFF_BUILDER_IMPORT_DATA_PATH . '/' . $id . '.json';
    }
}

if (!function_exists('onoff_builder_import_dir')) {
    function onoff_builder_import_dir($id)
    {
        if (!onoff_builder_validate_project_id($id)) {
            return false;
        }

        return ONOFF_BUILDER_IMPORTS_PATH . '/' . $id;
    }
}

if (!function_exists('onoff_builder_has_import')) {
    function onoff_builder_has_import($id)
    {
        $meta = onoff_builder_get_import($id);

        return is_array($meta) && !empty($meta['id']);
    }
}

if (!function_exists('onoff_builder_get_import')) {
    function onoff_builder_get_import($id)
    {
        if (!onoff_builder_validate_project_id($id)) {
            return null;
        }

        $path = onoff_builder_meta_path($id);

        if (!$path || !is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }
}

if (!function_exists('onoff_builder_get_imports')) {
    function onoff_builder_get_imports()
    {
        $list = array();

        if (!is_dir(ONOFF_BUILDER_IMPORT_DATA_PATH)) {
            return $list;
        }

        $files = glob(ONOFF_BUILDER_IMPORT_DATA_PATH . '/*.json');
        if (!$files) {
            return $list;
        }

        foreach ($files as $file) {
            $raw = @file_get_contents($file);
            if ($raw === false) {
                continue;
            }
            $data = json_decode($raw, true);
            if (!is_array($data) || empty($data['id'])) {
                continue;
            }
            $list[] = $data;
        }

        usort($list, function ($a, $b) {
            $ta = isset($a['updated_at']) ? $a['updated_at'] : '';
            $tb = isset($b['updated_at']) ? $b['updated_at'] : '';

            return strcmp($tb, $ta);
        });

        return $list;
    }
}

if (!function_exists('onoff_builder_save_import')) {
    function onoff_builder_save_import($data)
    {
        if (!is_array($data) || empty($data['id']) || !onoff_builder_validate_project_id($data['id'])) {
            return false;
        }

        if (!onoff_builder_ensure_dir(ONOFF_BUILDER_IMPORT_DATA_PATH)) {
            return false;
        }

        $path = onoff_builder_meta_path($data['id']);
        if (!$path) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        if (empty($data['created_at'])) {
            $data['created_at'] = $now;
        }
        $data['updated_at'] = $now;

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        return @file_put_contents($path, $json, LOCK_EX) !== false;
    }
}

if (!function_exists('onoff_builder_delete_import_meta')) {
    function onoff_builder_delete_import_meta($id)
    {
        if (!onoff_builder_validate_project_id($id)) {
            return false;
        }

        $path = onoff_builder_meta_path($id);

        if ($path && is_file($path)) {
            return @unlink($path);
        }

        return true;
    }
}
