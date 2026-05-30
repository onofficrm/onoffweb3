<?php
/**
 * iCRM — 게시글 최종 URL 조회·확정
 *
 * GET  {사이트 G5_URL}/icrm/final-url.php?bo_table=community&wr_id=123
 * POST 동일 (form 또는 JSON). Header: X-ICRM-Token: {이 사이트 secret}
 *
 * 도메인·토큰은 사이트마다 다름. thecebu 등 특정 도메인 하드코딩 없음.
 */
include_once __DIR__ . '/_bootstrap.php';

icrm_api_handle_resolve();
