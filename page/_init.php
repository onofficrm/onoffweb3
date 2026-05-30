<?php
/**
 * 서브페이지 공통 부트스트랩
 * - 직접 URL 접근: /page/about.php
 * - include 경로: dirname 기준 프로젝트 루트 _common.php 로드
 */
if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === '_init.php') {
    exit;
}

if (!defined('_GNUBOARD_')) {
    include_once(dirname(__FILE__).'/../_common.php');
}

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 서브페이지 시작 (head.php)
 * @param string $title 브라우저·container_title용
 */
function g5_page_start($title)
{
    global $g5;
    $g5['title'] = $title;
    include_once(G5_PATH.'/head.php');
}

/**
 * 서브페이지 종료 (tail.php)
 */
function g5_page_end()
{
    include_once(G5_PATH.'/tail.php');
}
