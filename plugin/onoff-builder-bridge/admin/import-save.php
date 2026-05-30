<?php
/** @deprecated upload.php + upload_update.php 사용 */
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);
onoff_builder_alert('ZIP 업로드는 upload.php 화면을 이용해 주세요.', onoff_builder_admin_url('upload.php'));
