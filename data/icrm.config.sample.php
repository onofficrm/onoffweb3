<?php
/**
 * iCRM API 비밀 설정 (사이트마다 1회 복사)
 *
 * cp data/icrm.config.sample.php data/icrm.config.php
 *
 * onoff-g5-base: 보통 비워 두면 G5_URL(그누보드 기본 URL)이 final_url 도메인이 되고,
 * 토큰은 첫 접속 시 data/icrm.config.php 가 자동 생성될 수 있습니다.
 * Git·고객 전달 ZIP에 data/icrm.config.php(실제 토큰)는 넣지 마세요.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

/** 비우면 G5_DOMAIN / G5_URL / 현재 접속 도메인 순으로 자동 (사이트마다 다름) */
define('ICRM_SITE_BASE_URL', '');

/** iCRM → 이 홈페이지 API 호출 시 (X-ICRM-Token 또는 ?token=) */
define('ICRM_SECRET_TOKEN', '');

/** iCRM 서버 고정 IP (쉼표·공백 구분). token 과 둘 중 하나만 통과 */
define('ICRM_ALLOWED_IPS', '');
