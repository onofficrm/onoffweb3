<?php
/** @deprecated upload.php 사용 */
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);
header('Location: ' . onoff_builder_admin_url('upload.php'));
exit;
