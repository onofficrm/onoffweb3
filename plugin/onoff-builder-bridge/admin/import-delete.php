<?php
/**
 * @deprecated list.php + delete.php 사용
 */
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);
onoff_builder_require_post();

$project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
$list_url = onoff_builder_admin_url('list.php');

$result = onoff_builder_delete_import($project_id);

if (empty($result['ok'])) {
    onoff_builder_alert(
        isset($result['message']) ? $result['message'] : '프로젝트 삭제에 실패했습니다.',
        $list_url
    );
}

$msg = isset($result['message']) ? $result['message'] : '프로젝트가 삭제되었습니다.';
$redirect = $list_url . '?msg=' . rawurlencode($msg);

if (function_exists('goto_url')) {
    goto_url($redirect);
}

header('Location: ' . $redirect);
exit;
