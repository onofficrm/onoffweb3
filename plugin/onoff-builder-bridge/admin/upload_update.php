<?php
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_admin(defined('G5_ADMIN_URL') ? G5_ADMIN_URL : G5_URL);
onoff_builder_require_post();

$project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
$project_name = isset($_POST['project_name']) ? trim(strip_tags($_POST['project_name'])) : '';

if ($project_name === '') {
    onoff_builder_alert('프로젝트 이름을 입력하세요.', onoff_builder_admin_url('upload.php'));
}

if (!isset($_FILES['zip_file'])) {
    onoff_builder_alert('ZIP 파일을 선택하세요.', onoff_builder_admin_url('upload.php'));
}

$result = onoff_builder_handle_zip_upload($project_id, $project_name, $_FILES['zip_file']);

if (empty($result['ok'])) {
    onoff_builder_alert(
        isset($result['message']) ? $result['message'] : '가져오기에 실패했습니다.',
        onoff_builder_admin_url('upload.php')
    );
}

$id = $result['project_id'];
$entry = isset($result['entry']) ? $result['entry'] : 'index.html';

if (!onoff_builder_add_import(array(
    'id'    => $id,
    'name'  => $result['project_name'],
    'path'  => $id,
    'entry' => $entry,
))) {
    onoff_builder_remove_dir(onoff_builder_project_dir($id));
    onoff_builder_alert('프로젝트 정보 저장에 실패했습니다. 다시 시도하세요.', onoff_builder_admin_url('upload.php'));
}

$msg = isset($result['message']) ? $result['message'] : '가져오기가 완료되었습니다.';

if (function_exists('goto_url')) {
    goto_url(onoff_builder_admin_url('list.php?msg=' . urlencode($msg)));
}

header('Location: ' . onoff_builder_admin_url('list.php?msg=' . urlencode($msg)));
exit;
