<?php
/**
 * @deprecated page.php?id= 사용 (관리자 미리보기도 동일 URL)
 */
include_once('./_common.php');
include_once(dirname(__FILE__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);

$id = isset($_GET['id']) ? $_GET['id'] : '';
if (onoff_builder_validate_project_id($id)) {
    header('Location: ' . onoff_builder_page_url(onoff_builder_sanitize_project_id($id)));
    exit;
}

onoff_builder_alert('page.php?id=프로젝트ID 형태로 접근해 주세요.', onoff_builder_admin_url('list.php'));
