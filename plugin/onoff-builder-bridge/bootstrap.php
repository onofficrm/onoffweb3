<?php
/**
 * onoff-builder-bridge — bootstrap (1단계: 기본 구조)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (defined('ONOFF_BUILDER_LOADED')) {
    return;
}

define('ONOFF_BUILDER_LOADED', true);
define('ONOFF_BUILDER_VERSION', '0.4.0-page-render');

if (defined('G5_PLUGIN_PATH')) {
    define('ONOFF_BUILDER_PATH', G5_PLUGIN_PATH . '/onoff-builder-bridge');
} else {
    define('ONOFF_BUILDER_PATH', dirname(__FILE__));
}

if (defined('G5_PLUGIN_URL')) {
    define('ONOFF_BUILDER_URL', G5_PLUGIN_URL . '/onoff-builder-bridge');
} else {
    define('ONOFF_BUILDER_URL', '/plugin/onoff-builder-bridge');
}

define('ONOFF_BUILDER_IMPORTS_PATH', ONOFF_BUILDER_PATH . '/imports');
define('ONOFF_BUILDER_IMPORTS_URL', ONOFF_BUILDER_URL . '/imports');
define('ONOFF_BUILDER_DATA_PATH', ONOFF_BUILDER_PATH . '/data');
define('ONOFF_BUILDER_IMPORTS_JSON', ONOFF_BUILDER_DATA_PATH . '/imports.json');
define('ONOFF_BUILDER_ASSETS_PATH', ONOFF_BUILDER_PATH . '/assets');
define('ONOFF_BUILDER_ASSETS_URL', ONOFF_BUILDER_URL . '/assets');

foreach (array(ONOFF_BUILDER_IMPORTS_PATH, ONOFF_BUILDER_DATA_PATH) as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

$lib_files = array('functions.php', 'importer.php');
foreach ($lib_files as $file) {
    $path = ONOFF_BUILDER_PATH . '/lib/' . $file;
    if (is_file($path)) {
        include_once $path;
    }
}
