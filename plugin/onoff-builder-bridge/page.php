<?php
/**
 * 빌더 dist 독립 페이지 출력 (head.php / tail.php 미사용)
 */
include_once(dirname(__FILE__) . '/_common.php');
include_once(dirname(__FILE__) . '/bootstrap.php');

$id = isset($_GET['id']) ? $_GET['id'] : '';

onoff_builder_render_import_page($id);
