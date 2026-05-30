<?php
if (!defined('_GNUBOARD_') && PHP_SAPI !== 'cli') exit;

return array(
    'id' => 'auto_comment',
    'name' => 'GnuBoard Auto Comment',
    'version' => '1.1.3',
    'requires' => array(
        'gnuboard' => '5.x',
        'php' => '5.6'
    ),
    'install_url' => 'plugin/auto_comment/install.php',
    'update_url' => 'plugin/auto_comment/update.php',
    'admin_url' => 'plugin/auto_comment/admin/index.php',
    'files' => array(
        'extend/auto_comment.extend.php',
        'plugin/auto_comment/admin/action.php',
        'plugin/auto_comment/admin/index.php',
        'plugin/auto_comment/auto_comment.lib.php',
        'plugin/auto_comment/build_package.php',
        'plugin/auto_comment/install.php',
        'plugin/auto_comment/make_full_package.php',
        'plugin/auto_comment/make_update_package.php',
        'plugin/auto_comment/manifest.php',
        'plugin/auto_comment/POINT_BILLING_PLAN.md',
        'plugin/auto_comment/README.md',
        'plugin/auto_comment/uninstall.php',
        'plugin/auto_comment/update.php'
    ),
    'data_paths' => array(
        'data/cache/auto_comment_last_run.php',
        'data/cache/auto_comment_worker.lock'
    )
);
