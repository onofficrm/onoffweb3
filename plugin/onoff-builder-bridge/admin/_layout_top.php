<?php
if (!defined('_GNUBOARD_')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo isset($obb_admin_title) ? onoff_builder_escape($obb_admin_title) : 'onoff-builder-bridge'; ?></title>
<link rel="stylesheet" href="<?php echo onoff_builder_escape(ONOFF_BUILDER_ASSETS_URL . '/css/admin.css'); ?>">
</head>
<body class="onoff-builder-admin">
<header class="onoff-builder-admin__header">
  <div class="onoff-builder-admin__inner">
    <h1 class="onoff-builder-admin__logo"><a href="<?php echo onoff_builder_admin_url(); ?>">onoff-builder-bridge</a></h1>
    <nav class="onoff-builder-admin__nav">
      <a href="<?php echo onoff_builder_admin_url(); ?>">대시보드</a>
      <a href="<?php echo onoff_builder_admin_url('import-list.php'); ?>">목록</a>
      <a href="<?php echo onoff_builder_admin_url('import-form.php'); ?>">가져오기</a>
      <a href="<?php echo onoff_builder_admin_url('settings.php'); ?>">설정</a>
      <a href="<?php echo onoff_builder_escape(G5_ADMIN_URL); ?>">그누보드 관리자</a>
    </nav>
  </div>
</header>
<main class="onoff-builder-admin__main">
  <div class="onoff-builder-admin__inner">
