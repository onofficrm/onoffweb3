<?php
include_once('./_common.php');
include_once(dirname(__FILE__) . '/bootstrap.php');

header('Content-Type: text/html; charset=utf-8');

if (onoff_builder_is_admin()) {
    header('Location: ' . onoff_builder_admin_url());
    exit;
}

onoff_builder_stub_message(
    'onoff-builder-bridge',
    '빌더 dist ZIP 업로드 플러그인입니다. 관리: admin/ · 출력: page.php?id=프로젝트ID'
);
